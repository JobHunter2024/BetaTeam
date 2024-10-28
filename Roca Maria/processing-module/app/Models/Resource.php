<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Resource extends Model
{
    protected $table = 'resources';

    protected $fillable = [
        'uri',
    ];

    public function triples()
    {
        return $this->hasMany(Triple::class);
    }

    public function getUri()
    {
        // Method declaration
    }

    public function getType()
    {
        // Method declaration
    }
}
