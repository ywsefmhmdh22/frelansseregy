<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    // دي الحتة اللي كانت ناقصة وموقفة الدنيا
    protected $fillable = [
    'user_id',
    'amount',
    'type',
    'payment_id',
    'payment_method', // ضيف السطر ده هنا
    'status',
    ];

    // علاقة الترانزاكشن باليوزر
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
