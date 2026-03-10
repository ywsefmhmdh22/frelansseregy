<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel; // تم التغيير لخاصة
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewMessageEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    /**
     * إنشاء نسخة جديدة من الحدث
     * $message هنا هو المصفوفة (Array) اللي بعتناها من الكنترولر
     */
    public function __construct($message)
    {
        $this->message = $message;
    }

    /**
     * تحديد القناة اللي الإشعار هيمشي فيها
     * تم تحويلها لـ PrivateChannel لضمان أن المستلم فقط هو من يراها
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.' . $this->message['receiver_id']),
        ];
    }

    /**
     * الاسم المستعار للحدث (Alias) اللي الجافا سكريبت بتدور عليه
     */
    public function broadcastAs()
    {
        return 'new-message';
    }

    /**
     * تحديد البيانات التي سيتم بثها (للتأكد من وصول كل التفاصيل)
     */
    public function broadcastWith()
    {
        return [
            'data' => $this->message
        ];
    }
}
