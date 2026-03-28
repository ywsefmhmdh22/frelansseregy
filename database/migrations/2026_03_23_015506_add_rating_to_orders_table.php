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
    Schema::table('orders', function (Blueprint $table) {
        $table->integer('rating')->nullable()->after('status');
        $table->text('comment')->nullable()->after('rating');
        $table->timestamp('completed_at')->nullable()->after('comment');
    });
}

public function down(): void
{
    Schema::table('orders', function (Blueprint $table) {
        $table->dropColumn(['rating', 'comment', 'completed_at']);
    });
}
};
