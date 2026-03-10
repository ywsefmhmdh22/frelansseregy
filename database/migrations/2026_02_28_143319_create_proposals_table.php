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
        Schema::create('proposals', function (Blueprint $table) {
            $table->id();

            // ربط العرض بالمشروع (لو اتمسح المشروع يتمسح العرض تلقائياً)
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');

            // ربط العرض بالمستقل (المستخدم) الذي قدمه
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // تفاصيل العرض المالية والزمنية
            $table->decimal('price', 10, 2);  // ميزانية العرض (قيمة العرض)
            $table->integer('duration');       // مدة التنفيذ بالأيام

            // وصف وتفاصيل العرض
            $table->text('description');       // الرسالة أو تفاصيل العرض المقدم

            // حالة العرض (قيد الانتظار، مقبول، مرفوض، مكتمل)
            $table->string('status')->default('pending');

            $table->timestamps();              // تاريخ التقديم والتحديث
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proposals');
    }
};
