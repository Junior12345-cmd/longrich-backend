<?php

namespace App\Models;

use App\Models\Shop;
use App\Models\Category;
use App\Models\Commande;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
       'user_id', 'title','description','price','quantity','category','shop_id','image','images','pays_disponibilite','sales','rating','is_global'
    ];

    protected $casts = [
        'images' => 'array',
        'pays_disponibilite' => 'array',
        'is_global' => 'boolean'
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category');
    }   

    public function commandes()
    {
        return $this->morphMany(Commande::class, 'orderable');
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
