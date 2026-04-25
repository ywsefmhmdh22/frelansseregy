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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            // المستخدم صاحب العملية
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // المبلغ
            $table->decimal('amount', 15, 2);

            // نوع العملية
            // deposit: شحن رصيد
            // withdraw: سحب رصيد
            // payment: دفع لمشروع
            // receive: استلام من مشروع
            // تم تغيير النوع إلى string لتجنب مشاكل التوافق مع الاستضافة
            $table->string('type');

            // حالة العملية
            // initialized: تم بدء العملية (جديد)
            // pending: قيد الانتظار
            // completed: تمت بنجاح
            // failed: فشلت
            // canceled: اتلغت (تأكد من توحيد الـ L)
            // تم تغيير النوع إلى string لضمان استيعاب الكلمات الطويلة مثل initialized
            $table->string('status')->default('initialized');

            // رقم العملية من بوابة الدفع (Paymob مثلا)
            $table->string('payment_id')->nullable();

            // طريقة الدفع
            // Vodafone Cash / Paymob / PayPal / Wallet
            $table->string('payment_method')->nullable();

            // تفاصيل إضافية (رقم محفظة - اسم المشروع - ملاحظات)
            $table->text('details')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
