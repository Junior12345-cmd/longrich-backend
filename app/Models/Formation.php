<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Formation extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'price',
        'format',
        'status',
        'image'
    ];

    // Relation avec les chapitres
    public function chapitres()
    {
        return $this->hasMany(Chapitre::class);
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

