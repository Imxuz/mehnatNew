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
        Schema::create('exam_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('vacancy_id')->constrained();
            $table->string('random_code',10);
            $table->boolean('access')->default(false);
            $table->timestamp('enter_time')->nullable();
            $table->string('exam_score',10)->nullable();
            $table->string('exam_result')->default('pending');
            $table->timestamps();
            $table->unique(['vacancy_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_users');
    }
};
