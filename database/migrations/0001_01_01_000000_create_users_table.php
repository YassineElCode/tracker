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
            $table->string('voornaam');
            $table->string('achternaam')->nullable();
            $table->string('rrn')->nullable();
            $table->string('gbdatum')->nullable();
            $table->string('whatsapp')->nullable();
            $table->boolean('send_notifications')->default(false);
            $table->string('tel')->nullable();
            $table->string('adres')->nullable();
            $table->string('postcode')->nullable();
            $table->string('zeersteVRijbewijsDatum')->nullable();
            $table->string('zhuidigVRijbewijsDatum')->nullable();
            $table->string('zhuidigVRijbewijsGeldigTot')->nullable();
            $table->string('startDatum')->nullable();
            $table->string('endDatum')->nullable();
            $table->string('startUur')->nullable();
            $table->string('endUur')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
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
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
