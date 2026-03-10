<?php

use Illuminate\Support\Facades\Broadcast;

// قناة الإشعارات الافتراضية لارفيل (لا تحذفها)
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// قناة الدردشة التي استعملتها في الـ JavaScript (مهمة جداً)
Broadcast::channel('chat.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
