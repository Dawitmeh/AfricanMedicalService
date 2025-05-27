<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fleet extends Model
{
    use HasFactory;


    protected $fillable = [
        'country_id',
        'name',
        'capacity',
        'classification',
        'icon',
        'Available',
        'Active',
        'description'
    ];


    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }
}
