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
        Schema::create('packs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('country_id')->constrained('countries')->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->integer('price');
            $table->text('features');
            $table->string('status')->default('actived'); // desactived, actived
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packs');
    }
};
