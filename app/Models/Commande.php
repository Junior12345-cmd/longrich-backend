<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commande extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference', 'customer', 'orderable_type', 'orderable_id', 'amount', 'status', 'transaction_id'
    ];

    protected $casts = [
        'customer' => 'array', // ou 'json'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
    public function orderable()
    {
        return $this->morphTo();
    }

    // public function customer()
    // {
    //     return $this->belongsTo(User::class, 'customer_id');
    // }
}
