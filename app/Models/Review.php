<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Review extends Model
{
    use HasFactory;

    // الحقول اللي مسموح نكتب فيها بيانات (ضفنا الأعمدة الجديدة هنا)
    protected $fillable = [
        'project_id',           // الحقل اللي كان عامل المشكلة
        'freelancer_id',
        'user_id',
        'rating_quality',       // معايير التقييم اللي زودناها في الميجريشن
        'rating_time',
        'rating_behavior',
        'rating_communication',
        'rating',               // التقييم النهائي
        'comment',
    ];

    /**
     * علاقة التقييم بالمشروع
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * علاقة التقييم بالمستقل
     */
    public function freelancer()
    {
        return $this->belongsTo(User::class, 'freelancer_id');
    }

    /**
     * علاقة التقييم بالعميل الذي قام بالتقييم
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
