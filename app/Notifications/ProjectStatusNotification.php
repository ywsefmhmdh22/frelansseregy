<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProjectStatusNotification extends Notification
{
    use Queueable;

    protected $projectTitle;
    protected $status; // 'approved' or 'deleted'
    protected $message;

    public function __construct($projectTitle, $status, $message = '')
    {
        $this->projectTitle = $projectTitle;
        $this->status = $status;
        $this->message = $message;
    }

    public function via($notifiable)
    {
        return ['database']; // سنخزن الإشعار في قاعدة البيانات ليظهر في الموقع
    }

    public function toArray($notifiable)
    {
        return [
            'title' => $this->status == 'approved' ? 'تمت الموافقة على مشروعك' : 'تم حذف مشروعك',
            'project' => $this->projectTitle,
            'message' => $this->message,
            'icon' => $this->status == 'approved' ? 'fas fa-check-circle text-success' : 'fas fa-exclamation-triangle text-danger',
        ];
    }
}
