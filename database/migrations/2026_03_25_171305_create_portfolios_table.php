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
    Schema::create('portfolios', function (Blueprint $table) {
        $table->id();
        // ربط العمل بالمستخدم (المستقل) اللي رفعه
        $table->foreignId('user_id')->constrained()->onDelete('cascade');

        $table->string('title'); // عنوان العمل (مثلاً: تصميم لوجو مطعم)
        $table->text('description'); // تفاصيل الشغل اللي عمله
        $table->string('image'); // صورة الشغل
        $table->string('link')->nullable(); // لو فيه رابط لموقع أو Behance
        $table->string('category')->nullable(); // فئة العمل (تصميم، برمجة، إلخ)
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portfolios');
    }
};
