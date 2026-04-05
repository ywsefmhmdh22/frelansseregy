<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// استدعاء الموديلات المرتبطة
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\Project;
use App\Models\Proposal;
use App\Models\Service;
use App\Models\Order;
use App\Models\Review;
use App\Models\Portfolio;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * الحقول القابلة للتعبئة (Mass Assignment)
     * تم إزالة الحقول الحساسة (role, balance, verification_status, is_banned)
     * لضمان عدم التلاعب بها من قبل المستخدم عبر طلبات HTTP
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'headline',
        'skills',
        'bio',
        'country',
        'city',
        'profile_image',
        'id_number',
        'id_image',
        'id_image_back',
        'is_profile_completed',
        'last_seen',
        'email_verified_at',
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
            'freelancer_rating' => 'float',
            'skills' => 'array',
        ];
    }

    // ================= العلاقات والوظائف =================

    /**
     * علاقة التقييمات المستلمة
     */
    public function reviews()
    {
        return $this->hasMany(Review::class, 'freelancer_id');
    }

    /**
     * علاقة معرض الأعمال
     */
    public function portfolios()
    {
        return $this->hasMany(Portfolio::class);
    }

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
     * علاقة الخدمات التي يمتلكها المستقل
     */
    public function services()
    {
        return $this->hasMany(Service::class);
    }

    /**
     * علاقة الخدمات المباعة (الطلبات)
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'seller_id');
    }

    /**
     * علاقة المبيعات
     */
    public function sales()
    {
        return $this->hasMany(Order::class, 'seller_id');
    }

    /**
     * علاقة التقييمات المستلمة
     */
    public function receivedReviews()
    {
        return $this->hasMany(Review::class, 'freelancer_id');
    }

    /**
     * التحقق هل المستخدم متصل الآن
     */
    public function isOnline()
    {
        return $this->last_seen && $this->last_seen->gt(now()->subMinutes(5));
    }

    /**
     * دالة مساعدة للتحقق من الأدمن (للأمان الإضافي)
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }
}
