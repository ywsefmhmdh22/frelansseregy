<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Message
     * جعلنا الخصائص protected عشان نتحكم في خروج البيانات من خلال broadcastWith فقط
     */
    protected $message;
    protected $user_name;

    public function __construct(Message $message)
    {
        // 1. التأكد من وجود البيانات وتجنب أي خطأ في حالة عدم وجود Sender
        $this->message = $message;
        $this->user_name = $message->sender ? $message->sender->name : 'Unknown User';
    }

    public function broadcastOn()
    {
        // 2. إرسال الإشعار فقط لقناة المستلم الخاصة (Private)
        return new PrivateChannel('chat.' . $this->message->receiver_id);
    }

    public function broadcastAs()
    {
        return 'new-message';
    }

    /**
     * الحماية القصوى هنا: تنظيف البيانات قبل إرسالها للـ Broadcast
     */
    public function broadcastWith(): array
    {
        return [
            'sender_id'  => (int) $this->message->sender_id,

            // 3. حماية XSS: تنظيف محتوى الرسالة من أي أكواد JS خبيثة
            'content'    => htmlspecialchars($this->message->message, ENT_QUOTES, 'UTF-8'),

            // 4. تنظيف اسم المستخدم أيضاً
            'user_name'  => htmlspecialchars($this->user_name, ENT_QUOTES, 'UTF-8'),

            'type'       => $this->message->type,
            'file_path'  => $this->message->file_path ? asset('storage/' . $this->message->file_path) : null,

            // 5. تنسيق الوقت بشكل آمن
            'created_at' => $this->message->created_at ? $this->message->created_at->format('H:i A') : now()->format('H:i A'),
        ];
    }
}
