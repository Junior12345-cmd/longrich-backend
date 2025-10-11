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
        Schema::create('formations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->constrained('users')->onDelete('cascade');;
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('image')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('format'); // ex: video, pdf, en ligne
            $table->string('status'); // ex: draft, published, archived
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('formations');
    }
};
