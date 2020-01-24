<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    //
    protected $fillable = [
        'name',
        'arabicName',
        'slug'
    ];

    public function cars() {
        return $this->hasMany('App\Car');
    }

    public function images() {
        return $this->belongsToMany('App\Image');
    }
}
