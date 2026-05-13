<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Resort extends Model
{
    protected $fillable = [
        'manager_id','name','description','status','category',
        'check_in_time','check_out_time','cancellation_policy','is_featured'
    ];

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function location()
    {
        return $this->hasOne(ResortLocation::class);
    }

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function photos()
    {
        return $this->hasMany(ResortPhoto::class)->orderBy('sort_order');
    }

    public function coverPhoto()
    {
        return $this->hasOne(ResortPhoto::class)->where('is_cover', true);
    }

    public function amenities()
    {
        return $this->belongsToMany(
            Amenity::class,
            'resort_amenities',  // pivot table name
            'resort_id',         // foreign key of this model
            'amenity_id'         // foreign key of related model
        );
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function approvalLogs()
    {
        return $this->hasMany(ResortApprovalLog::class);
    }

    public function isApproved(): bool { return $this->status === 'approved'; }
    public function isPending(): bool  { return $this->status === 'pending'; }
}
