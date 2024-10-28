<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class RDFNode extends Model
{
    protected $table = 'rdf_nodes';

    protected $fillable = [
        'value',
        'datatype',
    ];

    public function triples()
    {
        return $this->hasMany(Triple::class);
    }

    public function getValue()
    {
        // Method declaration
    }

    public function getDatatype()
    {
        // Method declaration
    }
}