<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'flag'
    ];

    protected $appends = ['flag_url'];


    public function getFlagUrlAttribute()
    {
        return asset('storage/' . $this->flag); // adjust path if needed
    }
}
