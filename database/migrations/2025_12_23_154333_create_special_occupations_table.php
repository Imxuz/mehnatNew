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
        Schema::create('special_occupations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained();
            $table->foreignId('occupation_id')->constrained();
            $table->json('title');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('special_demands');
    }
};
