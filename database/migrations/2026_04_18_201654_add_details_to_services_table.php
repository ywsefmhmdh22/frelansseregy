<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            // ضفنا شرط التحقق عشان لو العمود موجود ما يطلعش Error
            if (!Schema::hasColumn('services', 'type')) {
                $table->enum('type', ['public', 'normal', 'ready'])->default('normal')->after('image');
            }
            if (!Schema::hasColumn('services', 'ready_file')) {
                $table->string('ready_file')->nullable()->after('type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['type', 'ready_file']);
        });
    }
};
