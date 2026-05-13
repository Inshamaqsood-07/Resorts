<?php

namespace App\Http\Controllers\ResortManager;


use App\Http\Controllers\Controller;
use App\Models\Resort;
use App\Models\ResortManagerProfile;
use App\Models\ResortLocation;
use App\Models\ResortPhoto;
use App\Models\Room;
use App\Models\Booking;
use App\Models\Amenity;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ManagerController extends Controller
{
    private function myResort(): ?Resort
    {
        return Auth::user()->load(['resort.location','resort.rooms','resort.photos','resort.amenities'])->resort;
    }

    // Share notifications with all manager views
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
        $resort = $this->myResort();
        $stats  = [];

        if ($resort) {
            $stats = [
                'total_bookings'    => Booking::where('resort_id', $resort->id)->count(),
                'pending_bookings'  => Booking::where('resort_id', $resort->id)->where('status','pending')->count(),
                'confirmed_bookings'=> Booking::where('resort_id', $resort->id)->where('status','confirmed')->count(),
                'monthly_revenue'   => Booking::where('resort_id', $resort->id)
                                        ->where('status','confirmed')
                                        ->whereMonth('created_at', now()->month)
                                        ->sum('total_amount'),
            ];
        }

        $recentBookings = $resort ? Booking::where('resort_id', $resort->id)->with('client')->latest()->take(8)->get() : collect();
        
        // ✅ CHANGE HERE: Extract notifications separately
        // $notifications = Notification::where('user_id', Auth::id())->latest()->take(20)->get();
        // $unreadCount = Notification::where('user_id', Auth::id())->where('is_read', false)->count();

        return view('resort-manager.dashboard', compact('resort','stats','recentBookings'));
    }

    // ─── My Resort ────────────────────────────────────────────────────────────
    public function myResortShow()
    {
        $resort    = $this->myResort();
        $amenities = Amenity::all();
        
        // $notifications = Notification::where('user_id', Auth::id())->latest()->take(20)->get();
        // $unreadCount = Notification::where('user_id', Auth::id())->where('is_read', false)->count();
        
        return view('resort-manager.resort', compact('resort','amenities'));
    }

    // ─── My Resort - Update (FIXED) ─────────────────────────────────────────────
    public function updateResort(Request $request)
    {
         $resort = Resort::where('manager_id', Auth::id())->firstOrFail();

        // Rest of your validation...
        $request->validate([
            'name'                => 'required|string|max:200',
            'description'         => 'nullable|string',
            'check_in_time'       => 'nullable|string',
            'check_out_time'      => 'nullable|string',
            'cancellation_policy' => 'nullable|string',
            'city'                => 'required|string|max:100',
            'country'             => 'required|string|max:100',
            'address'             => 'nullable|string',
            'amenities'           => 'nullable|array',
            'amenities.*'         => 'exists:amenities,id',
        ]);
        // Update resort basic info
        $resort->update($request->only([
            'name', 'description', 'check_in_time', 'check_out_time', 'cancellation_policy'
        ]));

        // Update location
        $location = ResortLocation::firstOrNew(['resort_id' => $resort->id]);
        $location->fill([
            'city'    => $request->city,
            'country' => $request->country,
            'address' => $request->address,
        ]);
        $location->save();

        // FIXED: Sync amenities properly
        $amenityIds = $request->input('amenities', []);
        
        // Log for debugging (remove after testing)
        \Log::info('Amenities to sync: ', $amenityIds);
        
        // Sync the amenities
        $resort->amenities()->sync($amenityIds);
        
        // Verify if synced
        $syncedCount = $resort->amenities()->count();
        \Log::info('Amenities synced count: ' . $syncedCount);

        return back()->with('success', 'Resort details and amenities saved successfully!');
    }

    // ─── Photos ───────────────────────────────────────────────────────────────
    public function photos()
    {
        $resort = Resort::where('manager_id', Auth::id())->firstOrFail();
        $photos = ResortPhoto::where('resort_id', $resort->id)->orderBy('sort_order')->get();
        
        // $notifications = Notification::where('user_id', Auth::id())->latest()->take(20)->get();
        // $unreadCount = Notification::where('user_id', Auth::id())->where('is_read', false)->count();
        
        return view('resort-manager.photos', compact('resort','photos'));
    }

    public function uploadPhoto(Request $request)
    {
        $request->validate([
            'photo'     => 'required|image|mimes:jpg,jpeg,png,webp|max:3072',
            'caption'   => 'nullable|string|max:200',
            'is_cover'  => 'nullable|boolean',
        ]);

        $resort = Resort::where('manager_id', Auth::id())->firstOrFail();
        $filename = time() . '_' . $request->file('photo')->getClientOriginalName();
        $request->file('photo')->move(public_path('images'), $filename);

        if ($request->is_cover) {
            ResortPhoto::where('resort_id', $resort->id)->update(['is_cover' => false]);
        }

        $maxOrder = ResortPhoto::where('resort_id', $resort->id)->max('sort_order') ?? 0;

        ResortPhoto::create([
            'resort_id'  => $resort->id,
            'photo_url'  => 'images/' . $filename,
            'caption'    => $request->caption,
            'is_cover'   => $request->boolean('is_cover'),
            'sort_order' => $maxOrder + 1,
        ]);

        return back()->with('success', 'Photo uploaded successfully.');
    }

    public function deletePhoto(ResortPhoto $photo)
    {
        $resort = Resort::where('manager_id', Auth::id())->firstOrFail();
        if ($photo->resort_id !== $resort->id) abort(403);

        $fullPath = public_path($photo->photo_url);
        if (file_exists($fullPath)) unlink($fullPath);
        $photo->delete();

        return back()->with('success', 'Photo deleted.');
    }

    public function setCover(ResortPhoto $photo)
    {
        $resort = Resort::where('manager_id', Auth::id())->firstOrFail();
        if ($photo->resort_id !== $resort->id) abort(403);

        ResortPhoto::where('resort_id', $resort->id)->update(['is_cover' => false]);
        $photo->update(['is_cover' => true]);

        return back()->with('success', 'Cover photo set.');
    }

    // ─── Rooms ────────────────────────────────────────────────────────────────
    public function rooms()
    {
        $resort = Resort::where('manager_id', Auth::id())->firstOrFail();
        $rooms  = Room::where('resort_id', $resort->id)->get();
        
        // $notifications = Notification::where('user_id', Auth::id())->latest()->take(20)->get();
        // $unreadCount = Notification::where('user_id', Auth::id())->where('is_read', false)->count();
        
        return view('resort-manager.rooms', compact('resort','rooms'));
    }

    public function addRoom(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:100',
            'room_type'       => 'required|string',
            'price_per_night' => 'required|numeric|min:0',
            'max_occupancy'   => 'required|integer|min:1',
            'total_units'     => 'required|integer|min:1',
            'bed_type'        => 'nullable|string',
            'size_sqft'       => 'nullable|integer',
            'description'     => 'nullable|string',
        ]);

        $resort = Resort::where('manager_id', Auth::id())->firstOrFail();
        
        Room::create(array_merge($request->only([
            'name','room_type','price_per_night','max_occupancy',
            'total_units','bed_type','size_sqft','description'
        ]), ['resort_id' => $resort->id]));

        return back()->with('success', 'Room added successfully.');
    }

    public function updateRoom(Request $request, Room $room)
    {
        $resort = Resort::where('manager_id', Auth::id())->firstOrFail();
        if ($room->resort_id !== $resort->id) abort(403);

        $request->validate([
            'name'            => 'required|string|max:100',
            'room_type'       => 'required|string',
            'price_per_night' => 'required|numeric|min:0',
            'max_occupancy'   => 'required|integer|min:1',
            'total_units'     => 'required|integer|min:1',
        ]);

        $room->update($request->only([
            'name','room_type','price_per_night','max_occupancy',
            'total_units','bed_type','size_sqft','description','is_active'
        ]));

        return back()->with('success', 'Room updated.');
    }

    public function deleteRoom(Room $room)
    {
        $resort = Resort::where('manager_id', Auth::id())->firstOrFail();
        if ($room->resort_id !== $resort->id) abort(403);
        $room->delete();
        
        return back()->with('success', 'Room deleted.');
    }

    // ─── Bookings ─────────────────────────────────────────────────────────────
   public function bookings(Request $request)
    {
        $resort = Resort::where('manager_id', Auth::id())->firstOrFail();
        $query  = Booking::where('resort_id', $resort->id)->with(['client','room']);
        if ($request->status) $query->where('status', $request->status);
        $bookings = $query->latest()->paginate(15);
        
        // ✅ Yahan bhi change karo
        // $notifications = Notification::where('user_id', Auth::id())->latest()->take(20)->get();
        // $unreadCount = Notification::where('user_id', Auth::id())->where('is_read', false)->count();
        
        return view('resort-manager.bookings', compact('bookings','resort'));
    }

    public function confirmBooking(Booking $booking)
    {
        $resort = Resort::where('manager_id', Auth::id())->firstOrFail();
        if ($booking->resort_id !== $resort->id) abort(403);

        $booking->update(['status' => 'confirmed']);

        Notification::create([
            'user_id'      => $booking->client_id,
            'type'         => 'booking_confirmed',
            'title'        => 'Booking Confirmed',
            'message'      => "Your booking #{$booking->booking_reference} at {$resort->name} has been confirmed.",
            'related_id'   => $booking->id,
            'related_type' => 'booking',
        ]);

        return back()->with('success', 'Booking confirmed.');
    }

    public function cancelBooking(Request $request, Booking $booking)
    {
        $request->validate(['reason' => 'required|string|max:500']);
        $resort = Resort::where('manager_id', Auth::id())->firstOrFail();
        if ($booking->resort_id !== $resort->id) abort(403);

        $booking->update([
            'status'              => 'cancelled',
            'cancellation_reason' => $request->reason,
            'cancelled_at'        => now(),
        ]);

        Notification::create([
            'user_id'      => $booking->client_id,
            'type'         => 'booking_cancelled',
            'title'        => 'Booking Cancelled',
            'message'      => "Your booking #{$booking->booking_reference} has been cancelled. Reason: " . substr($request->reason, 0, 100),
            'related_id'   => $booking->id,
            'related_type' => 'booking',
        ]);

        return back()->with('success', 'Booking cancelled.');
    }

    // ─── Profile ──────────────────────────────────────────────────────────────
    public function profile()
    {
        $user = Auth::user()->load('managerProfile');
        
        // ✅ Yahan bhi change karo
        // $notifications = Notification::where('user_id', Auth::id())->latest()->take(20)->get();
        // $unreadCount = Notification::where('user_id', Auth::id())->where('is_read', false)->count();
        
        return view('resort-manager.profile', compact('user'));
    }

   // ─── Profile - Update (FIXED for photo upload) ──────────────────────────────
    public function updateProfile(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:100',
            'phone'     => 'nullable|string|max:20',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048', // Added validation
        ]);

        // Update basic info
        Auth::user()->update($request->only('full_name', 'phone'));

        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            $profile = ResortManagerProfile::firstOrCreate(['user_id' => Auth::id()]);
            
            // Delete old photo if exists
            if ($profile->profile_photo) {
                $oldPath = public_path('images/profiles/' . $profile->profile_photo);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
            
            // Upload new photo
            $file = $request->file('profile_photo');
            $filename = time() . '_' . Auth::id() . '.' . $file->getClientOriginalExtension();
            
            // Move file to public/images/profiles directory
            $file->move(public_path('images/profiles'), $filename);
            
            // Save filename to database
            $profile->update(['profile_photo' => $filename]);
        }

        return back()->with('success', 'Profile updated successfully.');
    }
    
    public function markNotificationsRead()
    {
        Notification::where('user_id', Auth::id())->update(['is_read' => true]);
        return response()->json(['success' => true]);
    }

    public function contact()
    {
        return view('resort-manager.contact');
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