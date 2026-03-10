<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();

            // 1. صاحب المشروع (العميل)
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // 2. المنفذ (المستقل) - تم إضافته هنا ليكون جاهزاً عند التوظيف
            $table->foreignId('freelancer_id')->nullable()->constrained('users')->onDelete('set null');

            // 3. تفاصيل المشروع الأساسية
            $table->string('title');
            $table->text('description');

            // 4. المالية والوقت
            $table->decimal('price', 15, 2);           // الميزانية الابتدائية
            $table->decimal('final_price', 15, 2)->nullable(); // المبلغ الفعلي المتفق عليه عند التوظيف
            $table->string('currency')->default('USD'); // العملة (مهمة جداً بناءً على الكنترولر بتاعك)
            $table->string('duration');                 // مدة التنفيذ

            // 5. المرفقات والحالة
            $table->string('image_url')->nullable();
            $table->string('type')->default('normal');  // عادي أو مميز

            // حالة المشروع (open, in_progress, pending_delivery, completed, closed)
            $table->string('status')->default('open');

            // حالة مراجعة الإدارة (pending, approved, rejected)
            $table->string('admin_status')->default('pending');
            $table->text('attachments')->nullable(); // لتخزين مسارات الملفات كمصفوفة

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
