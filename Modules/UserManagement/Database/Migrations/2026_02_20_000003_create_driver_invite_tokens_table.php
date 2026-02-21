<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Part D — Driver Invite Tokens
 * Stores magic-login invite links for new drivers.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_invite_tokens', function (Blueprint $table) {
            $table->id();
            $table->uuid('driver_id');
            $table->foreign('driver_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('token', 128)->unique();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('used')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_invite_tokens');
    }
};
