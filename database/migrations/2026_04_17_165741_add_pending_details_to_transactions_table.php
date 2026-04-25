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
    Schema::table('transactions', function (Blueprint $table) {
        // التحقق مما إذا كان العمود غير موجود قبل إضافته
        if (!Schema::hasColumn('transactions', 'currency')) {
            $table->string('currency', 10)->default('USD')->after('amount');
        }

        if (!Schema::hasColumn('transactions', 'unlock_at')) {
            $table->timestamp('unlock_at')->nullable()->after('currency');
        }

        if (!Schema::hasColumn('transactions', 'is_unlocked')) {
            $table->boolean('is_unlocked')->default(true)->after('unlock_at');
        }
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // حذف الفهرس أولاً
            $table->dropIndex(['user_id', 'status', 'release_at']);

            // حذف الأعمدة
            $table->dropColumn(['currency', 'release_at', 'source_id', 'source_type']);
        });
    }
};
