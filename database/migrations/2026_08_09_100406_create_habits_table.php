<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('habits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('description')->nullable();
            $table->string('emoji')->default('💪');
            $table->string('share_token', 32)->unique();
            $table->boolean('is_public')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('habits'); }
};
