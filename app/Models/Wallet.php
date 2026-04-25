<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    /**
     * الحقول المسموح بتعبئتها.
     * تم الإبقاء على user_id و balance لضمان التحكم في الرصيد.
     */
    protected $fillable = ['user_id', 'balance'];

    /**
     * علاقة المحفظة بالمستخدم.
     * كل محفظة تنتمي لمستخدم واحد.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * علاقة المحفظة بسجل العمليات (Transactions).
     * نربط هنا المحفظة بكافة العمليات المالية التي قام بها المستخدم صاحب المحفظة.
     */
    public function transactions()
    {
        // نستخدم user_id كمرابط بين الجدولين لأن العمليات تُسجل برقم المستخدم
        return $this->hasMany(Transaction::class, 'user_id', 'user_id');
    }

    /**
     * جلب العمليات المكتملة فقط (Completed Transactions).
     * هذه الدالة تساعدك في عرض السجل الحقيقي فقط الذي أثر على الرصيد.
     */
    public function completedTransactions()
    {
        return $this->hasMany(Transaction::class, 'user_id', 'user_id')
                    ->where('status', 'completed');
    }

    /**
     * جلب الرصيد بتنسيق مالي (مثلاً: 100.00 $).
     * بما أن نظامك يعتمد الدولار كعملة أساسية للمحفظة.
     */
    public function getFormattedBalanceAttribute()
    {
        return number_format($this->balance, 2) . ' $';
    }
}
