<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'currency_code',
        'phone_prefix',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
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


