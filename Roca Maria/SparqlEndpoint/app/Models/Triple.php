<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Triple extends Model
{
    protected $fillable = [
        'resource_id',
        'property_id',
        'rdf_node_id',
    ];

    public function dataset()
    {
        return $this->belongsTo(Dataset::class);
    }

    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function rdfNode()
    {
        return $this->belongsTo(RDFNode::class);
    }

    public function getSubject()
    {
        return $this->resource_id;
    }

    public function getPredicate()
    {
        return $this->property_id;
    }

    public function getObject()
    {
        return $this->rdf_node_id;
    }

}
