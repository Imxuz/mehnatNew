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
            $table->foreignId('dir_demand_id')->constrained();
            $table->foreignId('admin_id')->constrained();
            $table->foreignId('occupation_id')->constrained();
            $table->timestamps('open');
            $table->timestamps('close');
            $table->boolean('publication')->default(false);

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
