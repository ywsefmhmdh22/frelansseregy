<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    /**
     * الحقول المسموح بتعبئتها (Mass Assignment)
     * ضفت لك هنا كل الحقول اللي في الـ Migration عشان السيستم ميرفضش تسجيل البيانات
     */
    protected $fillable = [
        'user_id',
        'amount',
        'type',           // deposit, withdraw, payment, receive
        'status',         // pending, completed, failed, cancelled
        'payment_id',     // رقم العملية من بوابة الدفع (Paymob)
        'payment_method', // Vodafone Cash / Paymob / Wallet
        'details',        // تفاصيل إضافية (ملاحظات أو اسم المشروع)
    ];

    /**
     * علاقة العملية بالمستخدم (صاحب المال)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * (إضافة اختيارية احترافية)
     * دالة لتنسيق المبلغ ليظهر مع العملة في الـ Blade بسهولة
     */
    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 2) . ' ج.م';
    }
}
