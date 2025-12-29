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
        Schema::create('vacancies', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('region_id');
            $table->foreignId('admin_id')->constrained();
            $table->foreignId('occupation_id')->constrained();
            $table->string('specials')->nullable();
            $table->integer('publication')->nullable();
            $table->timestamp('open_at')->nullable();
            $table->timestamp('close_at')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vacancies');
    }
};
