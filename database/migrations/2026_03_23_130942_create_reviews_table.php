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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();

            // الربط بالمشروع والمنفذ والعميل
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('freelancer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // معايير التقييم التفصيلية (للحسابات المتقدمة)
            $table->integer('rating_quality')->default(5);
            $table->integer('rating_time')->default(5);
            $table->integer('rating_behavior')->default(5);
            $table->integer('rating_communication')->default(5);

            // التقييم النهائي (المتوسط)
            $table->decimal('rating', 3, 2);

            // نص التقييم
            $table->text('comment');

            // --- الإضافات التي طلبتها لخدمة العدادات ---
            // ملاحظة: يفضل أن تكون هذه العدادات في جدول الـ users لسرعة العرض،
            // ولكن إذا أردت تتبعها هنا في جدول التقييمات كحالة (Flag):
            $table->boolean('is_excellent')->virtualAs('rating >= 4.00'); // حقل افتراضي يحدد التميز تلقائياً

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
