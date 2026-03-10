<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    use HasFactory;

    // تحديد الجدول لو لارافيل معرفش يوصل له تلقائياً
    protected $table = 'favorites';

    // الحقول المسموح بكتابتها في الداتابيز
    protected $fillable = [
        'user_id',
        'freelancer_id',
    ];

    /**
     * علاقة المفضل بالمستقل (User)
     * هنا بنقول إن كل "مفضلة" بتنتمي لمستقل معين (مستخدم)
     */
    public function freelancer()
    {
        // بنستخدم موديل User لأن المستقل هو في الأصل مستخدم في النظام
        return $this->belongsTo(User::class, 'freelancer_id');
    }

    /**
     * علاقة المفضل بالعميل (الذي قام بالإعجاب)
     */
    public function client()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
