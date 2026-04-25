<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'currency',
        'converted_amount',
        'type',
        'status',
        'payment_id',
        'payment_method',
        'details',
        'unlock_at', // تم الإضافة لضمان إمكانية حفظ تاريخ فك الحجز
    ];

    /**
     * الـ Casts: ضرورية جداً لتحويل التاريخ من نص إلى كائن Carbon
     * وبدونها سيعطي الكنترولر خطأ عند استدعاء toIso8601String()
     */
    protected $casts = [
        'unlock_at' => 'datetime',
    ];

    /**
     * علاقة العملية بالمستخدم
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // =========================================================================
    // الـ Scopes: تصفية البيانات بسهولة في الـ Controller
    // =========================================================================

    /**
     * جلب العمليات الناجحة فقط
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * جلب العمليات التي تم إنشاؤها ولم تكتمل بعد (initialized)
     */
    public function scopeInitialized($query)
    {
        return $query->where('status', 'initialized');
    }

    /**
     * جلب العمليات المعلقة
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // =========================================================================
    // الـ Accessors: لتنسيق البيانات عند عرضها في الـ Blade
    // =========================================================================

    /**
     * تنسيق المبلغ الأصلي مع العملة (EGP/USD)
     */
    public function getFormattedAmountAttribute()
    {
        $symbol = $this->currency === 'USD' ? '$' : 'ج.م';
        return number_format($this->amount, 2) . ' ' . $symbol;
    }

    /**
     * تنسيق المبلغ الصافي المضاف للمحفظة بالدولار
     */
    public function getFormattedConvertedAmountAttribute()
    {
        return number_format($this->converted_amount ?? 0, 2) . ' $';
    }

    /**
     * إرجاع لون الـ Badge بناءً على الحالة
     */
    public function getStatusColorAttribute()
    {
        return [
            'completed'   => 'success',   // أخضر
            'initialized' => 'info',      // أزرق سماوي
            'pending'     => 'warning',   // أصفر
            'failed'      => 'danger',    // أحمر
            'canceled'    => 'secondary', // رمادي
        ][$this->status] ?? 'dark';
    }

    /**
     * ترجمة حالة العملية للعربية
     */
    public function getStatusArabicAttribute()
    {
        return [
            'completed'   => 'مكتملة',
            'initialized' => 'قيد البدء',
            'pending'     => 'قيد الانتظار',
            'failed'      => 'فاشلة',
            'canceled'    => 'ملغاة',
        ][$this->status] ?? 'غير معروف';
    }
}
