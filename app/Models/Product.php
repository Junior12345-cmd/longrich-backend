<?php

namespace App\Models;

use App\Models\Shop;
use App\Models\Category;
use App\Models\Commande;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
        return $this->belongsTo(Category::class);
    }

    public function commandes()
    {
        return $this->hasMany(Commande::class, 'product_id');
    }

}
