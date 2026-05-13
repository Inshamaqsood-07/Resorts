<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Amenity extends Model
{
    public $timestamps = false;

    protected $fillable = ['name','icon','category'];

    public function resorts()
    {
        return $this->belongsToMany(
            Resort::class,
            'resort_amenities',
            'amenity_id',
            'resort_id'
        );
    }
}
