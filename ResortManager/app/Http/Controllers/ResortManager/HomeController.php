<?php

namespace App\Http\Controllers;

use App\Models\Resort;
use App\Models\Room;
use App\Models\ContactMessage;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $query = Resort::where('status','approved')->with(['location','coverPhoto','rooms']);

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name','like',"%{$request->search}%")
                  ->orWhereHas('location', fn($l) => 
                      $l->where('city','like',"%{$request->search}%")
                        ->orWhere('country','like',"%{$request->search}%")
                  );
            });
        }

        if ($request->category) $query->where('category', $request->category);
        if ($request->location) {
            $query->whereHas('location', fn($l) => 
                $l->where('city','like',"%{$request->location}%")
                  ->orWhere('country','like',"%{$request->location}%")
            );
        }

        $resorts    = $query->latest()->paginate(12);
        $categories = Resort::where('status','approved')->distinct()->pluck('category')->filter();

        return view('home.index', compact('resorts','categories'));
    }

    public function resortDetail(Resort $resort)
    {
        if ($resort->status !== 'approved') abort(404);
        $resort->load(['location','photos','rooms','amenities','manager']);
        return view('home.resort-detail', compact('resort'));
    }

    public function contact()
    {
        return view('home.contact');
    }

    // FIXED: Double success message removed
    public function sendContact(Request $request)
    {
        $request->validate([
            'sender_name'  => 'required|string|max:100',
            'sender_email' => 'required|email',
            'sender_phone' => 'nullable|string|max:20',
            'subject'      => 'required|string|max:200',
            'message'      => 'required|string|max:2000',
        ]);

        $msg = ContactMessage::create($request->only('sender_name','sender_email','sender_phone','subject','message'));

        // Notify admin
        $admin = User::where('role','admin')->first();
        if ($admin) {
            Notification::create([
                'user_id'      => $admin->id,
                'type'         => 'contact_message',
                'title'        => 'New Contact Message',
                'message'      => $request->sender_name . ' sent a message: ' . substr($request->subject, 0, 50),
                'related_id'   => $msg->id,
                'related_type' => 'contact_message',
            ]);
        }

        // Single success message only
        return redirect()->route('contact')->with('success', 'Your message has been sent. We will get back to you within 24 hours.');
    }
}