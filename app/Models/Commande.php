<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; 

class Commande extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference', 'customer', 'orderable_type', 'orderable_id', 'amount', 'status', 'transaction_id', 'quantity', 'product_id', 'transaction', 'amount_with_taxe'
    ];

    protected $casts = [
        'customer' => 'array',
        'transaction' => 'array',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'orderable_id');
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
