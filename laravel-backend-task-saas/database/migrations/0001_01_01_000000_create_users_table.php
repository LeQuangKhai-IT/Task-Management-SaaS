<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations. OK
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('email')->unique();
            $table->string('fullname')->nullable();
            $table->timestamp('email_verified_at');
            $table->string('password')->nullable();
            $table->string('provider')->nullable();     // google, github, slack
            $table->string('provider_id')->nullable();  // id of user in provider
            $table->string('avatar_url')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->uuid('userID')->primary();
            $table->string('token');
            $table->timestamps();
        });

        // Store email address has not been verified
        Schema::create('email_verifications', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('verification_token', 64)->nullable()->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('email_verifications');
    }
};
