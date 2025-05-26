<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hospital extends Model
{
    use HasFactory;


    protected $fillable = [
        'country_id',
        'name',
        'location',
        'description',
        'image'
    ];


     public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }
}
