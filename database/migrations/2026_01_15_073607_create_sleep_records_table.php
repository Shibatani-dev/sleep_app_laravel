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
        Schema::create('sleep_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->time('bedtime');
            $table->time('wakeup');
            $table->decimal('hours', 4, 2);
            $table->integer('quality')->default(3);
            $table->text('memo')->nullable();
            $table->timestamp('input_time');
            $table->integer('trust_score')->default(100);
            $table->integer('points_earned')->default(0);
            $table->timestamps();

            // 同じ日に複数記録できないようにユニーク制約
            $table->unique(['user_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sleep_records');
    }
};
