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
        Schema::create('user_milestones', function (Blueprint $table) {
            $table->id();
            $table->string('milestone_type', 50); // users_1k, users_5k, users_10k, etc.
            $table->integer('threshold_value');
            $table->integer('actual_value');
            $table->boolean('notification_sent')->default(false);
            $table->timestamp('reached_at');
            $table->timestamps();

            $table->unique('milestone_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_milestones');
    }
};
