<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;


    protected $fillable = [
        'user_id',
        'type_id',
        'first_name',
        'last_name',
        'age',
        'country_code',
        'email',
        'phone',
        'image',
        'password',
        'status'
    ];



    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function userType()
    {
        return $this->belongsTo(ClientType::class, 'type_id');
    }

}
