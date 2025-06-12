<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;


    protected $fillable = [
        'client_id',
        'type_id',
        'document',
    ];


    public function client() 
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function documentType() 
    {
        return $this->belongsTo(DocumentType::class, 'type_id');
    }
}
