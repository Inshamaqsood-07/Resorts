<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class UserApprovalLog extends Model
{
    public $timestamps = false;

    protected $fillable = ['target_user_id','admin_id','action','reason'];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function targetUser() { return $this->belongsTo(User::class, 'target_user_id'); }
    public function admin()      { return $this->belongsTo(User::class, 'admin_id'); }
}
