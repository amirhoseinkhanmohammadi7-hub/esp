<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('user_name')->nullable();
            $table->string('session_id')->nullable(); // برای کاربران لاگین نکرده
            $table->string('reaction_type'); // emoji: 👍💪⭐
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('reactions');
    }
};
