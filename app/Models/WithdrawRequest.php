<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WithdrawRequest extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'amount', 'method', 'details', 'status'];

    /**
     * العلاقة التي كانت تنقصك:
     * ربط طلب السحب بالمستخدم الذي قام بالطلب
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
