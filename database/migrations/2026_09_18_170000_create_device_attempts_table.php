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
        Schema::create('device_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('device_fingerprint', 64)->unique()->index();
            $table->unsignedTinyInteger('sms_sent_today')->default(0);
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('last_sms_sent_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_attempts');
    }
};
