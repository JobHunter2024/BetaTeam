<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dataset extends Model
{
    protected $table = 'datasets';

    protected $fillable = [
        'name',
    ];

    public function triples()
    {
        return $this->hasMany(Triple::class);
    }

    public function getName()
    {
        // Method declaration
    }

    public function addTriple(Resource $resource, Property $property, RDFNode $rdfNode)
    {
        // Method declaration
    }
}