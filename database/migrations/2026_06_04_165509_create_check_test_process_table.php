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
        Schema::create('check_test_processes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id')->constrained('admins');
            $table->unsignedBigInteger('vacancy_id')->constrained('vacancies');
            $table->date('test_date');
            $table->integer('duration');
            $table->integer('total_hours');
            $table->enum('duration_unit', ['hours', 'days'])->default('days');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('check_test_process');
    }
};
