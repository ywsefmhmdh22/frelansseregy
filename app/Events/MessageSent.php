<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow; // أضفنا Now للسرعة القصوى
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    // بنسيبهم public عشان لارافيل Reverb يقدر يوصلهم بسهولة أو نخليهم كما كنت تفضل
    public $message;
    public $user_name;

    public function __construct(Message $message)
    {
        $this->message = $message;
        // التأكد من جلب الاسم بشكل آمن
        $this->user_name = $message->sender ? $message->sender->name : 'Unknown User';
    }

    public function broadcastOn()
    {
        // القناة الخاصة بالمستلم
        return new PrivateChannel('chat.' . $this->message->receiver_id);
    }

    public function broadcastAs()
    {
        // خلي الاسم موحد وسهل للـ JS
        return 'MessageSent';
    }

    public function broadcastWith(): array
    {
        return [
            'id'         => $this->message->id,
            'sender_id'  => (int) $this->message->sender_id,
            // حماية XSS اللي أنت عاملها ممتازة
            'content'    => htmlspecialchars($this->message->message, ENT_QUOTES, 'UTF-8'),
            'user_name'  => htmlspecialchars($this->user_name, ENT_QUOTES, 'UTF-8'),
            'type'       => $this->message->type,
            'file_path'  => $this->message->file_path ? asset('storage/' . $this->message->file_path) : null,
            'created_at' => $this->message->created_at ? $this->message->created_at->diffForHumans() : now()->diffForHumans(),
        ];
    }
}
