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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->integer('region_id')->nullable();
            $table->string("name")->nullable();
            $table->string("passport_series",2)->nullable();
            $table->string("passport_number",7)->nullable();
            $table->string("birthday",20)->nullable();
            $table->boolean("pass_verify")->default(false);
            $table->string("pinfl",14)->nullable();
            $table->string("address",255)->nullable();
            $table->string("phone", 12)->unique();
            $table->string("unique_id", 50)->nullable();
            $table->string('email', 100)->unique()->nullable();
            $table->string('verification_code', 6)->nullable();
            $table->boolean('agreement');
            $table->string('password');
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verification_code_expires_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('sessions');
    }
};
