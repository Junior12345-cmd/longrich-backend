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

        Schema::create('shops', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->constrained()->onDelete('cascade'); // FK vers users
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('logo')->nullable();
            $table->string('banner')->nullable();
            $table->string('adresse')->nullable();
            $table->string('mail')->nullable();
            $table->string('option')->nullable();
            $table->string('phone')->nullable();
            $table->string('category')->nullable();
            $table->string('status')->default('inactive');
            // $table->string('completion_status')->default('incomplete'); // complete/incomplete
            $table->decimal('solde', 15, 2)->default(0);
            $table->string('title_principal_shop')->nullable();
            $table->text('text_description_shop')->nullable();
            $table->text('text_bouton_shop')->nullable();
            $table->string('lien_shop')->nullable();
            $table->string('theme')->nullable();
            $table->text('seo_meta')->nullable();
            $table->text('template')->nullable();
            $table->integer('views_count')->default(0);
            $table->string('paymentOnDelivery')->default(false);
            $table->string('salesTax')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shops');
    }
};
