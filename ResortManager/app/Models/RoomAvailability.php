<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class RoomAvailability extends Model
{
    public $timestamps = false;
    protected $fillable = ['room_id','date','available_units','is_blocked','block_reason'];
    public function room() { return $this->belongsTo(Room::class); }
}
