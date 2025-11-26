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
        Schema::create('dir_demands', function (Blueprint $table) {
            $table->id();
            $table->json('title');
            $table->string("name",100);
            $table->integer('sort_number');
            $table->enum('type', ['text', 'file']);
            $table->enum('formType', ['input', 'select','checkbox']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dir_demands');
    }
};
