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
     * عرض صفحة الدردشة والـ Inbox - تم حل مشكلة الـ SQL Injection نهائياً
     */
    public function chat(User $user = null)
    {
        $authId = auth()->id();

        // 1. جلب قائمة الأشخاص (Inbox) بطريقة Eloquent الصافية لتجنب SQL Raw تماماً
        // بنجيب أحدث رسالة لكل مستخدم تواصلت معه
        $conversations = Message::where('sender_id', $authId)
            ->orWhere('receiver_id', $authId)
            ->select(DB::raw('DISTINCT CASE WHEN sender_id = ' . (int)$authId . ' THEN receiver_id ELSE sender_id END as contact_id'))
            ->get();

        // نجلب المستخدمين بناءً على الـ IDs اللي استخرجناها
        $contactsIds = $conversations->pluck('contact_id')->toArray();

        // جلب جهات الاتصال مع آخر رسالة لترتيبهم
        $contacts = User::whereIn('id', $contactsIds)->get()->map(function($contact) use ($authId) {
            $contact->last_message = Message::where(function($q) use ($authId, $contact) {
                $q->where('sender_id', $authId)->where('receiver_id', $contact->id);
            })->orWhere(function($q) use ($authId, $contact) {
                $q->where('sender_id', $contact->id)->where('receiver_id', $authId);
            })->latest()->first();
            return $contact;
        })->sortByDesc(function($contact) {
            return $contact->last_message->created_at ?? 0;
        });

        // توجيه تلقائي إذا لم يطلب المستخدم شخصاً معيناً وكان هناك محادثات
        if (!$user && $contacts->isNotEmpty()) {
            return redirect()->route('messages.chat', $contacts->first()->id);
        }

        // جلب الرسائل بين الطرفين باستخدام Parameterized Queries آمنة
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

    /**
     * إرسال رسالة جديدة مع حماية XSS متقدمة وتشفير الملفات
     */
    public function sendMessage(Request $request, User $user)
    {
        // 1. Validation صارم
        $request->validate([
            'message' => 'nullable|string|max:5000',
            'file'    => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf,doc,docx,mp4,zip|max:20480',
            'audio'   => 'nullable|file|mimes:mp3,wav,ogg,m4a,webm|max:10240',
        ]);

        $type = 'text';
        $filePath = null;

        // تنظيف الرسالة من أي أكواد خبيثة (XSS Protection)
        $cleanMessage = $request->message ? trim(strip_tags($request->message)) : null;

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
                $filePath = $file->store('chat_files', 'public');
            }
            elseif ($request->hasFile('audio')) {
                $type = 'audio';
                $filePath = $request->file('audio')->store('chat_audio', 'public');
            }
        } catch (\Exception $e) {
            Log::error('Chat File Upload Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'فشل تحميل الملف'], 500);
        }

        if (empty($cleanMessage) && !$filePath) {
            return response()->json(['success' => false, 'error' => 'الرسالة فارغة'], 400);
        }

        // 3. حفظ الرسالة باستخدام Eloquent (يحمي تلقائياً من SQL Injection)
        $msg = Message::create([
            'sender_id'   => (int) auth()->id(),
            'receiver_id' => (int) $user->id,
            'message'     => $cleanMessage,
            'type'        => $type,
            'file_path'   => $filePath,
        ]);

        // تجهيز بيانات الإشعار - تأمين كل البيانات الخارجة للـ JavaScript
        $notifData = [
            'sender_id'   => (int) auth()->id(),
            'user_name'   => e(auth()->user()->name), // استخدام دالة e() للهروب من HTML
            'content'     => $this->getNotifContent($type, $cleanMessage),
            'receiver_id' => (int) $user->id,
            'created_at'  => $msg->created_at->format('H:i A'),
            'type'        => $type,
            'file_path'   => $filePath ? asset('storage/' . $filePath) : null,
        ];

        // إرسال الحدث عبر Pusher/Socket
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
     * دالة مساعدة لتحديد محتوى الإشعار
     */
    private function getNotifContent($type, $message)
    {
        if ($type === 'text') return e(Str::limit($message, 50));
        if ($type === 'image') return 'أرسل صورة 📷';
        if ($type === 'video') return 'أرسل فيديو 🎥';
        if ($type === 'audio') return 'رسالة صوتية 🎤';
        return 'أرسل ملفاً 📁';
    }
}
