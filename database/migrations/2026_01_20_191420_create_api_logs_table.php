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
        Schema::create('api_logs', function (Blueprint $table) {
            $table->id();
            $table->string('type')->index(); // 'incoming', 'outgoing'
            $table->string('service')->index(); // 'openai', 'app', 'catalogue', etc.
            $table->string('method'); // GET, POST, etc.
            $table->string('endpoint');
            $table->string('status')->nullable(); // 'success', 'error', 'pending'
            $table->integer('status_code')->nullable();
            $table->json('request_headers')->nullable();
            $table->json('request_body')->nullable();
            $table->json('response_body')->nullable();
            $table->integer('duration_ms')->nullable(); // Response time in milliseconds
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_logs');
    }
};
