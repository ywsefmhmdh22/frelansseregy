<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    /**
     * عرض صفحة الدردشة والـ Inbox مع حماية SQL Injection
     */
    public function chat(User $user = null)
    {
        $authId = auth()->id();

        // جلب قائمة الأشخاص (Inbox) مرتبين حسب أحدث رسالة
        // تم استخدام الـ Bindings (?) لمنع الـ SQL Injection تماماً
        $conversations = Message::where('sender_id', $authId)
            ->orWhere('receiver_id', $authId)
            ->select([
                DB::raw('MAX(created_at) as last_msg_at'),
                DB::raw("CASE WHEN sender_id = ? THEN receiver_id ELSE sender_id END as contact_id", [$authId])
            ])
            ->groupBy('contact_id')
            ->orderBy('last_msg_at', 'desc')
            ->get();

        $contacts = User::whereIn('id', $conversations->pluck('contact_id'))->get();

        // توجيه تلقائي إذا لم يطلب المستخدم شخصاً معيناً وكان هناك محادثات
        if (!$user && $contacts->isNotEmpty()) {
            return redirect()->route('messages.chat', $contacts->first()->id);
        }

        // جلب الرسائل بين الطرفين
        $messages = collect();
        if ($user) {
            $receiverId = $user->id;
            $messages = Message::where(function($q) use ($authId, $receiverId) {
                $q->where('sender_id', $authId)->where('receiver_id', $receiverId);
            })->orWhere(function($q) use ($authId, $receiverId) {
                $q->where('sender_id', $receiverId)->where('receiver_id', $authId);
            })->orderBy('created_at', 'asc')->get();

            // تحديث حالة القراءة بطريقة آمنة
            Message::where('sender_id', $receiverId)
                   ->where('receiver_id', $authId)
                   ->where('is_read', false)
                   ->update(['is_read' => true]);
        }

        return view('chat.show', compact('user', 'messages', 'contacts'));
    }

    /**
     * إرسال رسالة جديدة مع حماية XSS متقدمة
     */
    public function sendMessage(Request $request, User $user)
    {
        // 1. Validation صارم للمدخلات والملفات
        $request->validate([
            'message' => 'nullable|string|max:5000',
            'file'    => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf,doc,docx,mp4,zip|max:20480',
            'audio'   => 'nullable|file|mimes:mp3,wav,ogg,m4a,webm|max:10240',
        ]);

        $type = 'text';
        $filePath = null;

        // --- حماية XSS المزدوجة ---
        // strip_tags بتشيل الـ HTML، و htmlspecialchars (بتتعمل في الـ Event) بتأمن العرض
        $cleanMessage = $request->message ? trim(strip_tags($request->message)) : null;

        // 2. معالجة الملفات مع التحقق من الـ MIME Type الفعلي
        try {
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
                // تخزين الملف باسم مشفر لزيادة الأمان
                $filePath = $file->store('chat_files', 'public');
            }
            elseif ($request->hasFile('audio')) {
                $type = 'audio';
                $filePath = $request->file('audio')->store('chat_audio', 'public');
            }
        } catch (\Exception $e) {
            Log::error('Chat File Upload Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Upload failed'], 500);
        }

        // منع الرسائل الفارغة
        if (empty($cleanMessage) && !$filePath) {
            return response()->json(['success' => false, 'error' => 'Message is empty'], 400);
        }

        // 3. حفظ الرسالة
        $msg = Message::create([
            'sender_id'   => auth()->id(),
            'receiver_id' => $user->id,
            'message'     => $cleanMessage,
            'type'        => $type,
            'file_path'   => $filePath,
        ]);

        // تجهيز بيانات الإشعار - تأمين كل البيانات الخارجة للـ JS
        $notifData = [
            'sender_id'   => (int) auth()->id(),
            'user_name'   => htmlspecialchars(auth()->user()->name, ENT_QUOTES, 'UTF-8'),
            'content'     => $this->getNotifContent($type, $cleanMessage, $filePath),
            'receiver_id' => (int) $user->id,
            'created_at'  => $msg->created_at->format('H:i A'),
            'type'        => $type,
            'file_path'   => $filePath ? asset('storage/' . $filePath) : null,
        ];

        // إرسال الحدث (Event) مع التأكد من وجود الكلاس
        if (class_exists(\App\Events\NewMessageEvent::class)) {
            event(new \App\Events\NewMessageEvent($notifData));
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message_data' => $notifData
            ]);
        }

        return back();
    }

    /**
     * دالة مساعدة لتحديد محتوى الإشعار (تأمين النص المختصر)
     */
    private function getNotifContent($type, $message, $path)
    {
        if ($type === 'text') return htmlspecialchars(Str::limit($message, 50), ENT_QUOTES, 'UTF-8');
        if ($type === 'image') return 'أرسل صورة 📷';
        if ($type === 'video') return 'أرسل فيديو 🎥';
        if ($type === 'audio') return 'رسالة صوتية 🎤';
        return 'أرسل ملفاً 📁';
    }
}
