<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'sender_name','sender_email','sender_phone','subject',
        'message','status','replied_by','reply_text','replied_at'
    ];

    protected $casts = [
        'replied_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function repliedBy() { return $this->belongsTo(User::class, 'replied_by'); }
}
