<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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

    // Génération automatique du UUID avant la création
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (! $model->id) {
                $model->id = (string) Str::uuid(); // UUID
            }
        });
    }

    public $incrementing = false;
    protected $keyType = 'string';
}

