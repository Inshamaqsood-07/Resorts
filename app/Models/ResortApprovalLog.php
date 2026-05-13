<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ResortApprovalLog extends Model
{
    public $timestamps = false;

    protected $fillable = ['resort_id','admin_id','action','notes'];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function resort() { return $this->belongsTo(Resort::class); }
    public function admin()  { return $this->belongsTo(User::class, 'admin_id'); }
}
