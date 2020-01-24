<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Feature extends Model
{
    //
    protected $fillable = [
        'englishFeature',
        'arabicFeature',
        'car_id'
    ];

    public function car() {
        return $this->belongsTo('App\Car');
    }
}
