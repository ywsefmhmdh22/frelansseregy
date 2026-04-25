<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->where('id', $id)->first();

        if ($notification) {
            $notification->markAsRead();

            // بنجيب الـ url اللي احنا لسه ضايفينه في الـ Notification فوق
            $url = $notification->data['url'] ?? route('home');
            return redirect($url);
        }

        return back();
    }
}
