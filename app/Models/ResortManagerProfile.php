<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ResortManagerProfile extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'user_id','cnic_number','business_license_no',
        'business_license_doc','profile_photo'
    ];
    public function user() { return $this->belongsTo(User::class); }
}
