<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
      public function up()
{
    Schema::create('orders', function (Blueprint $table) {
        $table->id();
        $table->foreignId('service_id')->constrained()->onDelete('cascade');
        $table->foreignId('buyer_id')->constrained('users')->onDelete('cascade');
        $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');

        $table->decimal('price', 10, 2);
        $table->string('payment_id')->nullable(); // مهم جداً للربط مع Paymob

        // الحالات لضمان الحقوق
        $table->enum('status', [
            'pending',    // بانتظار تأكيد الدفع
            'processing', // جاري التنفيذ (الفلوس محجوزة عند الموقع)
            'delivered',  // المستقل سلم الشغل (العميل بيراجعه دلوقتي)
            'completed',  // العميل استلم (الفلوس راحت للمستقل)
            'cancelled'   // الطلب اتلغى والفلوس رجعت للعميل
        ])->default('pending');

        $table->text('delivery_msg')->nullable(); // هنا المستقل بيحط رابط شغله
        $table->timestamp('due_date')->nullable();

        $table->timestamps();
    });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
