<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue; // اختياري للسرعة
use Illuminate\Notifications\Messages\BroadcastMessage; // مهم للبث
use Illuminate\Contracts\Broadcasting\ShouldBroadcast; // مهم جداً للـ Real-time

class ProjectStatusNotification extends Notification implements ShouldBroadcast
{
    use Queueable;

    protected $projectTitle;
    protected $status;
    protected $message;

    public function __construct($projectTitle, $status, $message = '')
    {
        $this->projectTitle = $projectTitle;
        $this->status = $status;
        $this->message = $message;
    }

    // نرسل الإشعار لقاعدة البيانات والبث المباشر معاً
    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    // بيانات قاعدة البيانات (التي ستظهر عند عمل ريفريش)
    public function toArray($notifiable)
    {
        return [
            'title'   => $this->status == 'approved' ? 'تمت الموافقة على مشروعك' : 'تم حذف مشروعك',
            'project' => $this->projectTitle,
            'message' => $this->message,
            'icon'    => $this->status == 'approved' ? 'fas fa-check-circle text-success' : 'fas fa-exclamation-triangle text-danger',
        ];
    }

    // بيانات البث الفوري (التي ستصل للمتصفح فوراً بدون ريفريش)
    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title'   => $this->status == 'approved' ? 'تهانينا! تمت الموافقة' : 'تنبيه بخصوص مشروعك',
            'project' => $this->projectTitle,
            'icon'    => $this->status == 'approved' ? 'fas fa-check-circle' : 'fas fa-times-circle',
            'url'     => url('/notifications'), // رابط يضغط عليه المستخدم
        ]);
    }
}
