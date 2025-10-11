<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Chapitre extends Model
{
    protected $fillable = [
        'title',
        'lien',
        'ressources',
        'formation_id',
        'description'
    ];

    // Relation avec la formation
    public function formation()
    {
        return $this->belongsTo(Formation::class);
    }

    // Si ressources est stocké en JSON
    protected $casts = [
        'ressources' => 'array',
    ];

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
