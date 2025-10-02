<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Packs extends Model
{
    protected $fillable = [
        'title',
        'description',
        'country_id',
        'price',
        'features',
        'status',
    ];

    // Si features est un JSON, on peut le caster automatiquement
    protected $casts = [
        'features' => 'array',
    ];

    // Relation avec le pays
    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }
}

