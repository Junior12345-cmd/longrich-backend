<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chapitre extends Model
{
    protected $fillable = [
        'title',
        'lien',
        'ressources',
        'formation_id',
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
}
