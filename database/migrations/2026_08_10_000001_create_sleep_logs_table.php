<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('sleep_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('log_date');
            $table->time('bedtime')->nullable(); // زمان خواب
            $table->time('wake_time')->nullable(); // زمان بیداری
            $table->integer('sleep_duration_minutes')->nullable(); // مدت خواب به دقیقه
            $table->integer('sleep_quality')->nullable(); // کیفیت خواب 1-5
            $table->text('note')->nullable(); // یادداشت اختیاری
            $table->timestamps();
            
            $table->unique(['user_id', 'log_date']);
        });
    }
    
    public function down(): void {
        Schema::dropIfExists('sleep_logs');
    }
};
