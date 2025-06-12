<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentType extends Model
{
    use HasFactory;


    protected $fillable = [
        'name',
        'format',
        'min_size',
        'max_size',
        'document_prefix',
        'dimension',
        'description'
    ];


    public function document() 
    {
        return $this->hasMany(Document::class, 'type_id');
    }
}
