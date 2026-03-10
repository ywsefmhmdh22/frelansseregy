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
        Schema::create('withdraw_requests', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->decimal('amount', 10, 2);
    $table->string('method'); // الوسيلة: instapay, vodafone_cash..
    $table->text('details');  // رقم التليفون أو الـ IBAN
    $table->string('status')->default('pending'); // حالة الطلب: pending, approved, rejected
    $table->timestamps();
    $table->string('currency')->default('EGP');
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdraw_requests');
    }
};
