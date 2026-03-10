<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ChatController extends Controller
{
    /**
     * عرض صفحة الدردشة والـ Inbox
     */
     // ... داخل ChatController ...

public function chat(User $user = null)
{
    $authId = auth()->id();

    // جلب قائمة الأشخاص (Inbox) مرتبين حسب أحدث رسالة
    $conversations = Message::where('sender_id', $authId)
        ->orWhere('receiver_id', $authId)
        ->select(DB::raw('MAX(created_at) as last_msg_at'),
                 DB::raw('CASE WHEN sender_id = ' . $authId . ' THEN receiver_id ELSE sender_id END as contact_id'))
        ->groupBy('contact_id')
        ->orderBy('last_msg_at', 'desc')
        ->get();

    $contacts = User::whereIn('id', $conversations->pluck('contact_id'))->get();

    // 2. تعديل: توجيه تلقائي فقط إذا لم يطلب المستخدم شخصاً معيناً وكان هناك محادثات
    if (!$user && $contacts->isNotEmpty()) {
        return redirect()->route('messages.chat', $contacts->first()->id);
    }

    // جلب الرسائل بين الطرفين (فقط إذا كان هناك مستخدم محدد)
    $messages = collect();
    if ($user) {
        $receiverId = $user->id;
        $messages = Message::where(function($q) use ($authId, $receiverId) {
            $q->where('sender_id', $authId)->where('receiver_id', $receiverId);
        })->orWhere(function($q) use ($authId, $receiverId) {
            $q->where('sender_id', $receiverId)->where('receiver_id', $authId);
        })->orderBy('created_at', 'asc')->get();

        // تحديث حالة القراءة
        Message::where('sender_id', $receiverId)
               ->where('receiver_id', $authId)
               ->where('is_read', false)
               ->update(['is_read' => true]);
    }

    return view('chat.show', compact('user', 'messages', 'contacts'));
}

public function sendMessage(Request $request, User $user)
{
    // 1. تحسين الـ Validation ليكون أكثر دقة
    $request->validate([
        'message' => 'nullable|string',
        'file'    => 'nullable|file|max:20480', // تحديد حجم 20 ميجا للملفات العادية
        'audio'   => 'nullable|file|max:10240', // 10 ميجا للصوت
    ]);

    $type = 'text';
    $filePath = null;

    // 2. معالجة الملفات (الصور، الفيديو، المستندات)
    if ($request->hasFile('file')) {
        $file = $request->file('file');
        $mime = $file->getMimeType();

        if (str_contains($mime, 'image')) {
            $type = 'image';
        } elseif (str_contains($mime, 'video')) {
            $type = 'video';
        } else {
            $type = 'file';
        }
        // استخدام storePublicly يسهل التعامل مع الصلاحيات أحياناً
        $filePath = $file->store('chat_files', 'public');
    }
    // معالجة الصوت (يأتي من الـ recorder كملف منفصل)
    elseif ($request->hasFile('audio')) {
        $type = 'audio';
        $filePath = $request->file('audio')->store('chat_audio', 'public');
    }

    // منع إرسال رسالة فارغة تماماً
    if (!$request->message && !$filePath) {
        return response()->json(['success' => false, 'error' => 'Empty message'], 400);
    }

    $msg = Message::create([
        'sender_id'   => auth()->id(),
        'receiver_id' => $user->id,
        'message'     => $request->message,
        'type'        => $type,
        'file_path'   => $filePath,
    ]);

    // تجهيز بيانات الإشعار
    $notifData = [
        'sender_id'   => auth()->id(),
        'user_name'   => auth()->user()->name,
        'content'     => $this->getNotifContent($type, $request->message, $filePath),
        'receiver_id' => $user->id,
        'created_at'  => $msg->created_at->format('H:i A'),
        'type'        => $type,
        'file_path'   => $filePath,
    ];

    event(new \App\Events\NewMessageEvent($notifData));

    if ($request->ajax() || $request->wantsJson()) {
        return response()->json([
            'success' => true,
            'message_data' => $notifData // نرسل نفس الداتا الموحدة
        ]);
    }

    return back();
}
}
