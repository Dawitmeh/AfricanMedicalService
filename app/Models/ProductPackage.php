<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductPackage extends Model
{
    use HasFactory;


    protected $fillable = [
        'product_id',
        'name',
        'total',
        'discount',
        'image',
        'description',
        
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
