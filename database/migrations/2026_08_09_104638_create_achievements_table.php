<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('habit_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('icon')->default('');
            $table->text('description')->nullable();
            $table->string('type');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('achievements'); }
};
