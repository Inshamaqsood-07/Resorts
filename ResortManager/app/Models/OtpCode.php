<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class OtpCode extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'email','otp','type','new_email','is_used','expires_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_used'    => 'boolean',
        'created_at' => 'datetime',
    ];
}
