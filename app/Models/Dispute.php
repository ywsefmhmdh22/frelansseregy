<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dispute extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'disputable_id',
        'disputable_type',
        'status',
        'admin_notes'
    ];

    // علاقة لجلب بيانات المشروع أو الخدمة بسهولة
    public function disputable()
    {
        return $this->morphTo();
    }

    // علاقة لجلب بيانات العميل اللي فتح النزاع
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
