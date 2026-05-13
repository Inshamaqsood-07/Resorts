<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Booking extends Model
{
    protected $fillable = [
        'booking_reference','client_id','resort_id','room_id',
        'check_in_date','check_out_date','total_nights',
        'guests_adults','guests_children','price_per_night_snapshot',
        'total_amount','status','special_requests',
        'cancellation_reason','cancelled_at'
    ];

    protected $casts = [
        'check_in_date'  => 'date',
        'check_out_date' => 'date',
        'cancelled_at'   => 'datetime',
    ];

    public function client() 
    { 
        return $this->belongsTo(User::class, 'client_id'); 
    }

    public function resort() 
    { 
        return $this->belongsTo(Resort::class); 
    }

    public function room()   
    { 
        return $this->belongsTo(Room::class); 
    }

    // Fixed: Client booking cancellation (confirmed bookings can also be cancelled 24 hours before check-in)
    public function canBeCancelledByClient(): bool
    {
        if (!in_array($this->status, ['pending', 'confirmed'])) {
            return false;
        }

        $now = Carbon::now();
        $checkIn = Carbon::parse($this->check_in_date)->startOfDay();

        if ($checkIn->isPast()) {
            return false;
        }

        // Use gt() comparison instead of diffInHours with false
        return $now->copy()->addHours(24)->lt($checkIn);
    }
    public static function generateReference(): string
    {
        return 'BK' . strtoupper(uniqid());
    }
}