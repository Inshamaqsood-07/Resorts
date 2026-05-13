<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ResortPhoto extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'resort_id','room_id','photo_url','caption','is_cover','sort_order'
    ];

    protected $casts = [
        'is_cover'    => 'boolean',
        'uploaded_at' => 'datetime',
    ];

    public function resort() { return $this->belongsTo(Resort::class); }
    public function room()   { return $this->belongsTo(Room::class); }
}
