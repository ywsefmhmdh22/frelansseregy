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
        Schema::table('wallets', function (Blueprint $table) {
            // إضافة عمود الرصيد المعلق بعد عمود الـ balance الأساسي
            // استخدمنا decimal لضمان دقة الحسابات المالية (15 رقم إجمالي، 2 بعد العلامة)
            $table->decimal('pending_balance', 15, 2)->default(0)->after('balance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            // حذف العمود في حال قررت عمل rollback
            $table->dropColumn('pending_balance');
        });
    }
};
