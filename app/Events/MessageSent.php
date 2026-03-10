<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\PrivateChannel; // غيرنا لـ Private
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
// ... الباقي كما هو

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public $user_name; // عشان تظهر في الإشعار

    public function __construct(Message $message)
    {
        $this->message = $message;
        $this->user_name = $message->sender->name;
    }

    public function broadcastOn()
    {
        // القناة لازم تكون متطابقة مع اللي في الـ JS
        // الـ JS بيسمع لـ Echo.private(`chat.${authId}`)
        // يبقى هنا نبعت للـ Receiver
        return new PrivateChannel('chat.' . $this->message->receiver_id);
    }

    public function broadcastAs()
    {
        return 'new-message';
    }

    public function broadcastWith()
    {
        // ابعت البيانات اللي الـ JS محتاجها بالضبط عشان ميتلخبطش
        return [
            'sender_id' => $this->message->sender_id,
            'content' => $this->message->message,
            'user_name' => $this->user_name,
            'type' => $this->message->type,
            'file_path' => $this->message->file_path,
            'created_at' => $this->message->created_at->format('H:i A'),
        ];
    }
}
