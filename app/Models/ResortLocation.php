<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ResortLocation extends Model
{
    public $timestamps = false;
    protected $fillable = ['resort_id','address','city','state_province','country','postal_code'];
    public function resort() { return $this->belongsTo(Resort::class); }
}
