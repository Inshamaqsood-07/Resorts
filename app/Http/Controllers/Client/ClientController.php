<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Resort;
use App\Models\Room;
use App\Models\Booking;
use App\Models\Notification;
use App\Models\ContactMessage;
use App\Models\ClientProfile;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    // Share notifications with all client views
    // public static function getNotifications()
    // {
    //     if (!auth()->check()) return (object)['notifications' => collect(), 'unreadCount' => 0];
        
    //     $notifications = Notification::where('user_id', auth()->id())->latest()->take(20)->get();
    //     $unreadCount = Notification::where('user_id', auth()->id())->where('is_read', false)->count();
        
    //     return (object)['notifications' => $notifications, 'unreadCount' => $unreadCount];
    // }

    // ─── Dashboard ────────────────────────────────────────────────────────────
    public function dashboard()
    {
        $user  = Auth::user()->load('clientProfile');
        $stats = [
            'total'     => Booking::where('client_id', Auth::id())->count(),
            'confirmed' => Booking::where('client_id', Auth::id())->where('status','confirmed')->count(),
            'pending'   => Booking::where('client_id', Auth::id())->where('status','pending')->count(),
            'cancelled' => Booking::where('client_id', Auth::id())->where('status','cancelled')->count(),
        ];

        $recentBookings = Booking::where('client_id', Auth::id())
            ->with(['resort','room'])->latest()->take(5)->get();
        
        // $notifData = self::getNotifications();

        return view('client.dashboard', compact('user','stats','recentBookings'));
    }

    // ─── Bookings ─────────────────────────────────────────────────────────────
    public function bookings(Request $request)
    {
        $query = Booking::where('client_id', Auth::id())->with(['resort','room']);
        if ($request->status) $query->where('status', $request->status);
        $bookings = $query->latest()->paginate(10);
        
        // $notifData = self::getNotifications();
        
        return view('client.bookings', compact('bookings'));
    }

    // Fixed: Client can cancel confirmed bookings (24 hours before check-in)
    public function cancelBooking(Request $request, Booking $booking)
    {
        if ($booking->client_id !== Auth::id()) abort(403);

        if (!$booking->canBeCancelledByClient()) {
            return back()->with('error', 'This booking cannot be cancelled (check-in is within 24 hours or booking is already completed/cancelled).');
        }

        $request->validate(['reason' => 'required|string|max:500']);

        $booking->update([
            'status'              => 'cancelled',
            'cancellation_reason' => $request->reason,
            'cancelled_at'        => now(),
        ]);

        // Notify resort manager
        Notification::create([
            'user_id'      => $booking->resort->manager_id,
            'type'         => 'booking_cancelled_by_client',
            'title'        => 'Booking Cancelled by Client',
            'message'      => "Client cancelled booking #{$booking->booking_reference}. Reason: " . substr($request->reason, 0, 100),
            'related_id'   => $booking->id,
            'related_type' => 'booking',
        ]);

        return back()->with('success', 'Booking cancelled successfully.');
    }

    // ─── Make a Booking ───────────────────────────────────────────────────────
    public function showBookingForm(Resort $resort, Room $room)
    {
        if ($resort->status !== 'approved') abort(404);
        
        // $notifData = self::getNotifications();
        
        return view('client.book', compact('resort','room'));
    }

    public function storeBooking(Request $request, Resort $resort, Room $room)
    {
        $request->validate([
            'check_in_date'   => 'required|date|after_or_equal:today',
            'check_out_date'  => 'required|date|after:check_in_date',
            'guests_adults'   => 'required|integer|min:1',
            'guests_children' => 'nullable|integer|min:0',
            'special_requests' => 'nullable|string|max:1000',
        ]);

        $checkIn  = Carbon::parse($request->check_in_date);
        $checkOut = Carbon::parse($request->check_out_date);
        $nights   = $checkIn->diffInDays($checkOut);
        $total    = $nights * $room->price_per_night;

        $booking = Booking::create([
            'booking_reference'        => Booking::generateReference(),
            'client_id'                => Auth::id(),
            'resort_id'                => $resort->id,
            'room_id'                  => $room->id,
            'check_in_date'            => $request->check_in_date,
            'check_out_date'           => $request->check_out_date,
            'total_nights'             => $nights,
            'guests_adults'            => $request->guests_adults,
            'guests_children'          => $request->guests_children ?? 0,
            'price_per_night_snapshot' => $room->price_per_night,
            'total_amount'             => $total,
            'status'                   => 'pending',
            'special_requests'         => $request->special_requests,
        ]);

        // Notify manager
        Notification::create([
            'user_id'      => $resort->manager_id,
            'type'         => 'new_booking',
            'title'        => 'New Booking Request',
            'message'      => Auth::user()->full_name . " has requested a booking for {$nights} night(s). Ref: {$booking->booking_reference}",
            'related_id'   => $booking->id,
            'related_type' => 'booking',
        ]);

        return redirect()->route('client.bookings')->with('success', "Booking submitted! Reference: {$booking->booking_reference}");
    }

    // ─── Check Availability ───────────────────────────────────────────────────
    public function checkAvailability(Request $request, Resort $resort, Room $room)
    {
        $checkIn  = $request->check_in;
        $checkOut = $request->check_out;

        if (!$checkIn || !$checkOut) {
            return response()->json(['available' => true]);
        }

        $conflict = Booking::where('room_id', $room->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where(function($q) use ($checkIn, $checkOut) {
                $q->whereBetween('check_in_date', [$checkIn, $checkOut])
                  ->orWhereBetween('check_out_date', [$checkIn, $checkOut])
                  ->orWhere(function($q2) use ($checkIn, $checkOut) {
                      $q2->where('check_in_date', '<=', $checkIn)
                         ->where('check_out_date', '>=', $checkOut);
                  });
            })->exists();

        return response()->json(['available' => !$conflict]);
    }

    // ─── Profile ──────────────────────────────────────────────────────────────
    public function profile()
    {
        $user = Auth::user()->load('clientProfile');
        // $notifData = self::getNotifications();
        
        return view('client.profile', compact('user'));
    }

    // Fixed: Handle date_of_birth properly
    public function updateProfile(Request $request)
    {
        $request->validate([
            'full_name'     => 'required|string|max:100',
            'phone'         => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender'        => 'nullable|string',
            'nationality'   => 'nullable|string|max:100',
            'city'          => 'nullable|string|max:100',
            'country'       => 'nullable|string|max:100',
            'address'       => 'nullable|string',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        Auth::user()->update(['full_name' => $request->full_name, 'phone' => $request->phone]);

        $profile = ClientProfile::firstOrCreate(['user_id' => Auth::id()]);
        
        $profileData = [
            'gender'      => $request->gender,
            'nationality' => $request->nationality,
            'city'        => $request->city,
            'country'     => $request->country,
            'address'     => $request->address,
        ];
        
        // Fix date_of_birth - handle safely
        if ($request->filled('date_of_birth')) {
            try {
                $profileData['date_of_birth'] = Carbon::parse($request->date_of_birth)->format('Y-m-d');
            } catch (\Exception $e) {
                // If parsing fails, skip
            }
        }

        if ($request->hasFile('profile_photo')) {
            if ($profile->profile_photo) {
                $oldPath = public_path('images/profiles/' . $profile->profile_photo);
                if (file_exists($oldPath)) unlink($oldPath);
            }
            $filename = time() . '_' . Auth::id() . '.' . $request->file('profile_photo')->getClientOriginalExtension();
            $request->file('profile_photo')->move(public_path('images/profiles'), $filename);
            $profileData['profile_photo'] = $filename;
        }

        $profile->update($profileData);

        return back()->with('success', 'Profile updated.');
    }

    public function markNotificationsRead()
    {
        Notification::where('user_id', Auth::id())->update(['is_read' => true]);
        return response()->json(['success' => true]);
    }

    // Submit contact message from client dashboard
    public function submitContact(Request $request)
    {
        $request->validate([
            'sender_name'  => 'required|string|max:100',
            'sender_email' => 'required|email',
            'sender_phone' => 'nullable|string|max:20',
            'subject'      => 'required|string|max:200',
            'message'      => 'required|string|max:2000',
        ]);

        // Create message
        $msg = \App\Models\ContactMessage::create([
            'sender_name'  => $request->sender_name,
            'sender_email' => $request->sender_email,
            'sender_phone' => $request->sender_phone,
            'subject'      => $request->subject,
            'message'      => $request->message,
            'status'       => 'unread',
        ]);

        // Notify admin
        $admin = \App\Models\User::where('role', 'admin')->first();
        if ($admin) {
            \App\Models\Notification::create([
                'user_id'      => $admin->id,
                'type'         => 'contact_message',
                'title'        => 'New Contact Message',
                'message'      => $request->sender_name . ' sent a message: ' . substr($request->subject, 0, 50),
                'related_id'   => $msg->id,
                'related_type' => 'contact_message',
            ]);
        }

        return back()->with('success', 'Your message has been sent. We will get back to you within 24 hours.');
    }
}