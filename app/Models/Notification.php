<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id','type','title','message',
        'is_read','related_id','related_type'
    ];

    protected $casts = [
        'is_read'    => 'boolean',
        'created_at' => 'datetime',
    ];

    public function user() { return $this->belongsTo(User::class); }
}
