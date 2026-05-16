<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ServicePurchasedNotification extends Notification
{
    use Queueable;

    protected $service;
    protected $sellerName;

    /**
     * نمرر بيانات الخدمة واسم البائع عند إنشاء الإشعار
     */
    public function __construct($service, $sellerName)
    {
        $this->service = $service;
        $this->sellerName = $sellerName;
    }

    /**
     * تحديد قنوات الإرسال: هنا اخترنا database فقط
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * البيانات التي سيتم تحويلها لـ JSON وتخزينها في عمود data في الجدول
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'تم شراء خدمة جديدة بنجاح',
            'message' => "لقد قمت بشراء خدمة: " . $this->service->title,
            'seller_name' => $this->sellerName,
            'amount' => $this->service->price,
            'service_id' => $this->service->id,
            'time' => now()->format('Y-m-d H:i:s'),
        ];
    }
}
