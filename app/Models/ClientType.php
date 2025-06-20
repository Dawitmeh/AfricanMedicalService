<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientType extends Model
{
    use HasFactory;


    protected $fillable = [
        'name',
        'description'
    ];


    public function client()
    {
        return $this->hasMany(Client::class, 'type_id');
    }
}
