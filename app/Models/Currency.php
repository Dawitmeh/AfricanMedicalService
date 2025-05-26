<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;


    protected $fillable = [
        'country_id',
        'name'
    ];


    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function product()
    {
        return $this->hasMany(Product::class, 'currency_id');
    }
}
