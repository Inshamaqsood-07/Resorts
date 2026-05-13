<?php
// ClientProfile.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ClientProfile extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'user_id','date_of_birth','gender','nationality',
        'city','country','address','profile_photo'
    ];
    public function user() { return $this->belongsTo(User::class); }
}
