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
        Schema::create('wallets', function (Blueprint $table) {
    $table->id();
    // بنربط المحفظة باليوزر، ولو اليوزر اتمسح المحفظة تتمسح (cascade)
    $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');

    // المبلغ: بنستخدم decimal عشان الدقة المالية (15 رقم منهم 2 بعد العلامة)
    // يعني يقدر يشيل لحد 99,999,999,999.99 جنيه
    $table->decimal('balance', 15, 2)->default(0.00);

    $table->timestamps(); // عشان نعرف المحفظة اتعملت إمتى
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
