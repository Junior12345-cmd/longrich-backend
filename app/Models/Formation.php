<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Formation extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'price',
        'format',
        'status',
    ];

    // Relation avec les chapitres
    public function chapitres()
    {
        return $this->hasMany(Chapitre::class);
    }
}

