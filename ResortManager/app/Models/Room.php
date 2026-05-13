<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'resort_id','name','description','room_type','max_occupancy',
        'total_units','price_per_night','size_sqft','bed_type','is_active'
    ];

    protected $casts = [
        'is_active'       => 'boolean',
        'price_per_night' => 'decimal:2',
        'created_at'      => 'datetime',
    ];

    public function resort()       { return $this->belongsTo(Resort::class); }
    public function photos()       { return $this->hasMany(ResortPhoto::class); }
    public function bookings()     { return $this->hasMany(Booking::class); }
    public function availability() { return $this->hasMany(RoomAvailability::class); }
}
