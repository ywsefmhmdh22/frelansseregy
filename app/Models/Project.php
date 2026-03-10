<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    /**
     * الحقول المسموح بتعبئتها آلياً (Mass Assignment)
     */
    protected $fillable = [
        'user_id',
        'freelancer_id',
        'title',
        'description',
        'price',
        'currency',
        'duration',
        'image_url',
        'attachments',    // تم إضافة الحقل هنا ليقبل التخزين
        'type',
        'status',
        'admin_status',
        'final_price',
    ];

    /**
     * تحويل الحقول تلقائياً (Casting)
     * هذا الجزء ضروري جداً لأنك اخترت تخزين المرفقات في نفس الجدول
     */
    protected $casts = [
        'attachments' => 'array', // يحول الـ JSON في القاعدة إلى مصفوفة PHP تلقائياً والعكس
    ];

    /**
     * علاقة المشروع بالمستخدم (العميل صاحب المشروع)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * علاقة المشروع بالمستقل (المنفذ)
     */
    public function freelancer()
    {
        return $this->belongsTo(User::class, 'freelancer_id');
    }

    /**
     * علاقة العروض (Proposals) المقدمة على هذا المشروع
     */
    public function proposals()
    {
        return $this->hasMany(Proposal::class);
    }

    /**
     * التحقق هل المشروع معتمد من الإدارة
     */
    public function isApproved()
    {
        return $this->admin_status === 'approved';
    }

    /**
     * الحصول على رابط الصورة كاملاً
     * تدعم الروابط الخارجية (CDN) والروابط المحلية (Storage)
     */
    public function getFullImageUrlAttribute()
    {
        if ($this->image_url) {
            if (filter_var($this->image_url, FILTER_VALIDATE_URL)) {
                return $this->image_url;
            }
            return asset('storage/' . $this->image_url);
        }

        // صورة افتراضية احترافية تحمل أول حرف من العنوان
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->title) . '&background=f8fafc&color=10b981&size=512';
    }

    /**
     * دالة مساعدة للحصول على السعر مع رمز العملة
     */
    public function getFormattedPriceAttribute()
    {
        return number_format($this->price, 2) . ' ' . ($this->currency ?? '$');
    }

    /**
     * دالة مساعدة للحصول على روابط المرفقات بشكل جاهز للاستخدام
     */
    public function getAttachmentUrlsAttribute()
    {
        if (!$this->attachments || !is_array($this->attachments)) {
            return [];
        }

        return array_map(function ($path) {
            return asset('storage/' . $path);
        }, $this->attachments);
    }
}
