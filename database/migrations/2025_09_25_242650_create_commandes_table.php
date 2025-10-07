<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('commandes', function (Blueprint $table) {
            $table->id();
            $table->longText('customer')->nullable();
            $table->morphs('orderable');             
            $table->integer('amount');
            $table->decimal('amount_with_taxe', 10, 2)->nullable();
            $table->integer('quantity')->default(1);
            $table->longText('transaction')->nullable();
            $table->string('reference')->unique();
            $table->string('transaction_id')->nullable();   
            $table->string('status')->default('pending'); // pending, completed, cancelled
            $table->timestamps();
        });    
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commandes');
    }
};
