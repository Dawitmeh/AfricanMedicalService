<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;


    protected $fillable = [
        'currency_id',
        'name',
        'cost',
        'price',
        'image',
        'description',
        'active'
    ];


    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function package()
    {
        return $this->hasMany(ProductPackage::class, 'product_id');
    }
}
