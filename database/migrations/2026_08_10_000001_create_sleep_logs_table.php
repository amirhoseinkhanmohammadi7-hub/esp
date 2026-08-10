<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    public function up(): void 
    {
        Schema::create('sleep_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('log_date');
            $table->time('bedtime')->nullable();
            $table->time('wake_time')->nullable();
            $table->integer('sleep_duration_minutes')->nullable();
            $table->string('sleep_quality')->nullable(); // Changed to string for enum values
            $table->text('note')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'log_date']);
        });
    }

    public function down(): void 
    {
        Schema::dropIfExists('sleep_logs');
    }
};
