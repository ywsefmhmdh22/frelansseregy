<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            // --- نظام الرول ---
            $table->enum('role', ['freelancer', 'client', 'admin'])->default('client');

            // --- البيانات الشخصية (اللي كانت في الملف الصغير) ---
            $table->string('profile_image')->nullable();
            $table->string('headline')->nullable();
            $table->text('skills')->nullable();
            $table->text('bio')->nullable();
            $table->string('phone')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();

            // --- عدادات النخبة ---
            $table->decimal('freelancer_rating', 3, 2)->default(0.00);
            $table->integer('total_projects_completed')->default(0);
            $table->integer('excellent_projects_count')->default(0);
            $table->integer('total_reviews')->default(0);

            // --- المحفظة والهوية ---
            $table->decimal('balance', 15, 2)->default(0.00);
            $table->string('id_number')->nullable();
            $table->string('id_image')->nullable();
            $table->string('id_image_back')->nullable();
            // خليتها Enum هنا عشان تطابق ملف التوثيق بتاعك
            $table->enum('verification_status', ['unverified', 'pending', 'verified', 'rejected'])->default('unverified');
            $table->boolean('is_profile_completed')->default(false);

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

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
