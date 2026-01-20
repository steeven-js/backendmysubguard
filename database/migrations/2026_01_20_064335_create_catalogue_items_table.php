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
        Schema::create('catalogue_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('typical_amount', 10, 2)->nullable();
            $table->string('logo_url')->nullable();
            $table->string('category')->default('Autre');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalogue_items');
    }
};
