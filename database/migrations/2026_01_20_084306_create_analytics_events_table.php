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
        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();
            $table->string('anonymous_user_id', 64)->index(); // SHA256 hash
            $table->string('event_type', 50)->index(); // app_downloaded, first_subscription_added, etc.
            $table->json('properties')->nullable(); // Additional event data
            $table->string('app_version', 20)->nullable();
            $table->string('os_version', 20)->nullable();
            $table->string('device_model', 50)->nullable();
            $table->timestamp('event_timestamp');
            $table->timestamps();

            $table->index(['event_type', 'event_timestamp']);
            $table->index(['anonymous_user_id', 'event_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_events');
    }
};
