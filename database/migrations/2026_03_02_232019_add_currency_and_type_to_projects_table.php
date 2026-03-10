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
        Schema::table('projects', function (Blueprint $table) {
            // التحقق من عمود العملة قبل إضافته
            if (!Schema::hasColumn('projects', 'currency')) {
                $table->string('currency', 10)->default('USD')->after('id'); // يمكنك تغيير الموقع حسب الرغبة
            }

            // التحقق من عمود النوع (الذي يسبب المشكلة حالياً)
            if (!Schema::hasColumn('projects', 'type')) {
                $table->string('type')->default('regular')->after('currency');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // حذف الأعمدة في حال عمل Rollback
            $columns = [];

            if (Schema::hasColumn('projects', 'currency')) {
                $columns[] = 'currency';
            }

            if (Schema::hasColumn('projects', 'type')) {
                $columns[] = 'type';
            }

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
