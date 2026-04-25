<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * إضافة نظام نوع الخدمة وملفات التسليم الفوري
     */
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            // 1. إضافة نوع الخدمة
            // ضفت 'public' هنا عشان الصفوف الـ 3 اللي عندك حالياً ما تسببش مشكلة في قاعدة البيانات
            // وضفت 'normal' و 'ready' عشان يشتغلوا مع الفورم الجديد بتاعك
            $table->enum('type', ['public', 'normal', 'ready'])->default('normal')->after('image');

            // 2. إضافة حقل مسار "ملف التسليم الفوري" (الخزنة)
            // الحقل nullable لأن الخدمات العادية (normal/public) مش محتاجة ملف تسليم مسبق
            $table->string('ready_file')->nullable()->after('type');
        });
    }

    /**
     * Reverse the migrations.
     * التراجع عن الإضافات
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            // حذف الأعمدة في حالة عمل rollback
            $table->dropColumn(['type', 'ready_file']);
        });
    }
};
