<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    // ضفنا هنا كل الحقول اللي في الميجريشن عشان الـ Laravel يوافق يسجلها
    protected $fillable = [
        'service_id',
        'buyer_id',
        'seller_id',
        'price',
        'status',
        'payment_id',
        'delivery_msg',
        'rating',
        'comment',
        'completed_at'
    ];

    // علاقة الطلب بالخدمة
    public function service() {
        return $this->belongsTo(Service::class);
    }

    // علاقة الطلب بالمشتري (العميل)
    public function buyer() {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    // علاقة الطلب بالبائع (المستقل)
    public function seller() {
        return $this->belongsTo(User::class, 'seller_id');
    }
}
