<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Resource extends Model
{
    protected $fillable = [
        'uri',
        'type', // Ensure 'type' is fillable if it's used.
    ];

    public function triples()
    {
        return $this->hasMany(Triple::class, 'resource_id'); // Foreign key if different from 'resource_id'
    }

    public function getUri()
    {
        return $this->uri;
    }

    public function getType()
    {
        return $this->type;
    }
}
