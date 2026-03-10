<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * الحقول القابلة للتعبئة (Mass Assignment)
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'skills',
        'bio',
        'country',
        'city',
        'profile_image',
        'balance',
        'id_number',
        'id_image',
        'id_image_back',
        'verification_status',
        'is_profile_completed',
        'last_seen',
        'is_banned',
        'freelancer_rating', // تم الإضافة للتقييم
        'total_reviews',    // تم الإضافة لإحصاء التقييمات
    ];

    /**
     * الحقول المخفية
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * تحويل أنواع البيانات (Casting)
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_profile_completed' => 'boolean',
            'is_banned' => 'boolean',
            'last_seen' => 'datetime',
            'balance' => 'decimal:2',
            'freelancer_rating' => 'float', // تحويل التقييم لرقم عشري
        ];
    }

    // ================= العلاقات والوظائف =================

    /**
     * علاقة المستخدم بالمحفظة (واحد لواحد)
     */
    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    /**
     * علاقة المستخدم بالعمليات المالية (واحد لمتعدد)
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * علاقة المستخدم بالمشاريع (كصاحب مشروع)
     */
    public function projects()
    {
        return $this->hasMany(Project::class, 'user_id');
    }

    /**
     * علاقة المستخدم بالمشاريع التي ينفذها (كمستقل)
     */
    public function freelancerProjects()
    {
        return $this->hasMany(Project::class, 'freelancer_id');
    }

    /**
     * علاقة العروض (Proposals) التي قدمها المستقل
     */
    public function proposals()
    {
        return $this->hasMany(Proposal::class);
    }

    /**
     * التحقق هل المستخدم متصل الآن
     */
    public function isOnline()
    {
        return $this->last_seen && $this->last_seen->gt(now()->subMinutes(5));
    }
    //
    public function services()
{
    return $this->hasMany(Service::class);
}
}
