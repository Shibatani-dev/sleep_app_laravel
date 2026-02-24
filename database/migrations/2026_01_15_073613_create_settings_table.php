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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constained()->onDelete('cascade');
            $table->time('target_bedtime')->default('23:00:00');
            $table->time('target_wakeup')->default('07:00:00');
            $table->decimal('target_sleep_hours', 3, 1)->default(7.5);
            $table->boolean('reminder_enabled')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
