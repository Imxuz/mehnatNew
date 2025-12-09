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
        Schema::create('doc_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('dir_demand_id')->constrained();
            $table->string('path')->nullable();
            $table->string('adder_demands_id')->nullable();
            $table->boolean('check')->nullable(); //for AI check results
            $table->text('doc_info')->nullable(); //for AI check results
            $table->string('description')->nullable(); //for AI check results

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doc_users');
    }
};
