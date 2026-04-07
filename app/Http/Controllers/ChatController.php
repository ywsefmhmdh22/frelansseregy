<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Class ChatController
 * المسؤول عن إدارة نظام المراسلة الفورية، تبادل الملفات، والـ Inbox.
 * تم تحصين الكود ضد هجمات SQL Injection و XSS لضمان أمان المحادثات.
 */
class ChatController extends Controller
{
    /**
     * عرض صفحة الدردشة والـ Inbox مع تأمين الاستعلامات.
     * * @param  \App\Models\User|null  $user
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function chat(User $user = null)
    {
        $authId = auth()->id();

        // 1. جلب قائمة الأشخاص (Inbox) باستخدام Parameterized Queries لمنع SQL Injection
        $conversations = Message::where('sender_id', $authId)
            ->orWhere('receiver_id', $authId)
            ->select(DB::raw('DISTINCT CASE WHEN sender_id = ? THEN receiver_id ELSE sender_id END as contact_id', [$authId]))
            ->get();

        $contactsIds = $conversations->pluck('contact_id')->toArray();

        // جلب جهات الاتصال مع آخر رسالة لترتيبهم برمجياً
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

        // توجيه تلقائي لأول محادثة إذا لم يتم تحديد مستخدم
        if (!$user && $contacts->isNotEmpty()) {
            return redirect()->route('messages.chat', $contacts->first()->id);
        }

        $messages = collect();
        if ($user) {
            $receiverId = $user->id;
            // جلب الرسائل بترتيب زمني تصاعدي
            $messages = Message::where(function($q) use ($authId, $receiverId) {
                $q->where('sender_id', $authId)->where('receiver_id', $receiverId);
            })->orWhere(function($q) use ($authId, $receiverId) {
                $q->where('sender_id', $receiverId)->where('receiver_id', $authId);
            })->orderBy('created_at', 'asc')->get();

            // تحديث حالة القراءة للرسائل المستلمة فقط
            Message::where('sender_id', $receiverId)
                   ->where('receiver_id', $authId)
                   ->where('is_read', false)
                   ->update(['is_read' => true]);
        }

        return view('chat.show', compact('user', 'messages', 'contacts'));
    }

    /**
     * إرسال رسالة جديدة مع حماية XSS صارمة وتشفير مسارات الملفات.
     * * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user المستلم
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function sendMessage(Request $request, User $user)
    {
        // 1. Validation صارم لجميع أنواع الوسائط
        $request->validate([
            'message' => 'nullable|string|max:5000',
            'file'    => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf,doc,docx,mp4,zip|max:20480',
            'audio'   => 'nullable|file|mimes:mp3,wav,ogg,m4a,webm|max:10240',
        ]);

        $type = 'text';
        $filePath = null;

        // 2. حماية XSS: تنظيف الرسالة من أي وسوم HTML نهائياً قبل الحفظ
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
                // تخزين آمن باسم مشفر
                $filePath = $file->store('chat_files', 'public');
            }
            elseif ($request->hasFile('audio')) {
                $type = 'audio';
                $filePath = $request->file('audio')->store('chat_audio', 'public');
            }
        } catch (\Exception $e) {
            Log::error('Chat File Upload Security Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'فشل تحميل الملف بشكل آمن'], 500);
        }

        // منع إرسال بيانات فارغة
        if (empty($cleanMessage) && !$filePath) {
            return response()->json(['success' => false, 'error' => 'لا يمكن إرسال رسالة فارغة'], 400);
        }

        // 3. الحفظ الفعلي باستخدام Eloquent (تأمين تلقائي ضد SQL Injection)
        $msg = Message::create([
            'sender_id'   => (int) auth()->id(),
            'receiver_id' => (int) $user->id,
            'message'     => $cleanMessage, // النص المنظف
            'type'        => $type,
            'file_path'   => $filePath,
        ]);

        // 4. تجهيز بيانات الإشعار مع تطبيق الـ Encoding (e function) لضمان سلامة الـ JavaScript
        $notifData = [
            'sender_id'   => (int) auth()->id(),
            'user_name'   => e(auth()->user()->name), // هروب من HTML
            'content'     => $this->getNotifContent($type, $cleanMessage),
            'receiver_id' => (int) $user->id,
            'created_at'  => $msg->created_at->format('H:i A'),
            'type'        => $type,
            'file_path'   => $filePath ? asset('storage/' . $filePath) : null,
        ];

        // إطلاق حدث الرسالة الجديدة (Real-time)
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
     * دالة مساعدة لتحديد محتوى الإشعار مع تأمين النصوص المعروضة.
     * * @param string $type
     * @param string|null $message
     * @return string
     */
    private function getNotifContent($type, $message)
    {
        if ($type === 'text') {
            // استخدام e() و Str::limit لضمان عرض نص آمن ومختصر
            return e(Str::limit($message, 50));
        }

        $placeholders = [
            'image' => 'أرسل صورة 📷',
            'video' => 'أرسل فيديو 🎥',
            'audio' => 'رسالة صوتية 🎤',
            'file'  => 'أرسل ملفاً 📁',
        ];

        return $placeholders[$type] ?? 'رسالة جديدة';
    }
}
