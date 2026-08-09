<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('habits', function (Blueprint $table) {
            $table->boolean('is_completed')->default(false);
            $table->text('completion_story')->nullable();
            $table->date('completed_at')->nullable();
        });
    }
    public function down(): void {
        Schema::table('habits', function (Blueprint $table) {
            $table->dropColumn(['is_completed', 'completion_story', 'completed_at']);
        });
    }
};
