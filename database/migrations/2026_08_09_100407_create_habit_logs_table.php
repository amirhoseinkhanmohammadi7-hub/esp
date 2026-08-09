<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('habit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('habit_id')->constrained()->onDelete('cascade');
            $table->date('log_date');
            $table->enum('type', ['micro', 'full']); // micro = 2 دقیقه, full = کامل
            $table->text('note')->nullable();
            $table->timestamps();
            $table->unique(['habit_id', 'log_date']);
        });
    }
    public function down(): void { Schema::dropIfExists('habit_logs'); }
};
