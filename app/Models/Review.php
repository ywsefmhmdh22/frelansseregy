<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class Review
 * الموديل المسؤول عن تخزين تقييمات المشاريع ومعايير الأداء للمستقلين.
 */
class Review extends Model
{
    use HasFactory;

    /**
     * الحقول القابلة للتعبئة (Mass Assignment)
     * تم إضافة المعايير التفصيلية لضمان توافقها مع نظام التقييم الجديد.
     */
    protected $fillable = [
        'project_id',
        'freelancer_id',
        'user_id',
        'rating_quality',      // جودة العمل المستلم
        'rating_time',         // الالتزام بالوقت المحددة
        'rating_behavior',     // التعامل والاحترافية
        'rating_communication', // سرعة ووضوح التواصل
        'rating',              // المتوسط العام المحسوب (التقييم النهائي)
        'comment',             // النص المكتوب للتقييم
    ];

    /**
     * علاقة التقييم بالمشروع المرتبط به.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * علاقة التقييم بالمستقل (المستخدم الذي تم تقييمه).
     */
    public function freelancer()
    {
        return $this->belongsTo(User::class, 'freelancer_id');
    }

    /**
     * علاقة التقييم بالعميل (المستخدم الذي قام بكتابة التقييم).
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
