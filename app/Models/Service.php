<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Service extends Model
{
    /**
     * الحقول القابلة للتعبئة بشكل جماعي.
     * تم إضافة 'type' و 'ready_file' لضمان عمل نظام الخدمات الجاهزة.
     */
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'price',
        'image',
        'type',        // النوع (normal أو ready)
        'ready_file',  // مسار الملف القابل للتحميل
        'status'
    ];

    /**
     * علاقة الخدمة بصاحبها (المستقل).
     * كل خدمة تنتمي لمستخدم واحد.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
