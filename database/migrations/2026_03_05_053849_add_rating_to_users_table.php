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
        Schema::table('users', function (Blueprint $table) {
            // إضافة أعمدة التقييم لجدول المستخدمين
            $table->integer('freelancer_rating')->default(0)->after('role');
            $table->integer('total_reviews')->default(0)->after('freelancer_rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // حذف الأعمدة في حالة عمل Rollback
            $table->dropColumn(['freelancer_rating', 'total_reviews']);
        });
    }
};
