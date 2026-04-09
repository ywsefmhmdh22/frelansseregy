<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    /**
     * الحقول المسموح بتعبئتها (Mass Assignment)
     * تم إضافة الحقول اللازمة لعملية التحويل المالي وتتبع العملات
     */
    protected $fillable = [
        'user_id',
        'amount',
        'currency',         // أضفنا هذا لتخزين نوع العملة (EGP/USD)
        'converted_amount', // أضفنا هذا لتخزين المبلغ الصافي الذي دخل المحفظة فعلياً
        'type',             // deposit, withdraw, payment, receive
        'status',           // pending, completed, failed, cancelled
        'payment_id',       // رقم الطلب (Order ID) من Paymob
        'payment_method',   // card, wallet
        'details',          // لتخزين الـ Transaction ID الخاص بـ Paymob كمرجع إضافي
    ];

    /**
     * علاقة العملية بالمستخدم
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * دالة لتنسيق المبلغ مع العملة المختارة في العملية
     */
    public function getFormattedAmountAttribute()
    {
        $symbol = $this->currency === 'USD' ? '$' : 'ج.م';
        return number_format($this->amount, 2) . ' ' . $symbol;
    }

    /**
     * دالة لتنسيق المبلغ الصافي المضاف للمحفظة (دائماً بالدولار حسب نظامك)
     */
    public function getFormattedConvertedAmountAttribute()
    {
        return number_format($this->converted_amount, 2) . ' $';
    }
}
