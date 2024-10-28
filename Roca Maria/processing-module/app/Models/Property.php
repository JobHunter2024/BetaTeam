<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    protected $table = 'properties';

    protected $fillable = [
        'uri',
        'label',
    ];

    public function triples()
    {
        return $this->hasMany(Triple::class);
    }

    public function getUri()
    {
        // Method declaration
    }

    public function getLabel()
    {
        // Method declaration
    }
}