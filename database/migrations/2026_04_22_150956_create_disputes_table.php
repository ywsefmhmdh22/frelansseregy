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
        Schema::create('disputes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // العميل اللي فتح النزاع

            // الربط المتعدد (Polymorphic) عشان يشتغل مع المشاريع والخدمات في نفس الجدول
            $table->unsignedBigInteger('disputable_id');
            $table->string('disputable_type');

            $table->string('status')->default('pending'); // حالة النزاع (pending, open, resolved, closed)
            $table->text('admin_notes')->nullable(); // ملاحظات الإدارة عند الحكم
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disputes');
    }
};
