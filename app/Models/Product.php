<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'title','description','price','qte','category_id','shop_id','image','images','pays_disponibilite','is_global'
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
}
