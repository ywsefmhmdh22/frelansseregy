<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Message;
use App\Events\MessageSent; // استدعاء الحدث الجديد
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

/**
 * Class ChatController
 * نظام مراسلة آمن متوافق مع معايير OWASP.
 * يدعم البث اللحظي (Real-time) عبر Laravel Reverb.
 */
class ChatController extends Controller
{
    /**
     * عرض صفحة الدردشة والـ Inbox.
     */
    public function chat(User $user = null)
    {
        $authId = (int) auth()->id();

        // 1. جلب قائمة المحادثات (Inbox)
        $conversations = Message::where('sender_id', $authId)
            ->orWhere('receiver_id', $authId)
            ->selectRaw("DISTINCT CASE WHEN sender_id = $authId THEN receiver_id ELSE sender_id END as contact_id")
            ->get();

        $contactsIds = $conversations->pluck('contact_id')->filter()->toArray();

        // 2. جلب جهات الاتصال
        $contacts = User::whereIn('id', $contactsIds)
            ->select('id', 'name', 'profile_image')
            ->get()
            ->map(function($contact) use ($authId) {
                $contact->last_message = Message::where(function($query) use ($authId, $contact) {
                        $query->where('sender_id', $authId)->where('receiver_id', (int)$contact->id);
                    })
                    ->orWhere(function($query) use ($authId, $contact) {
                        $query->where('sender_id', (int)$contact->id)->where('receiver_id', $authId);
                    })
                    ->latest()
                    ->first();
                return $contact;
            })
            ->sortByDesc(function($contact) {
                return $contact->last_message->created_at ?? 0;
            });

        if (!$user && $contacts->isNotEmpty()) {
            return redirect()->route('messages.chat', $contacts->first()->id);
        }

        $messages = collect();
        if ($user) {
            $receiverId = (int) $user->id;

            $messages = Message::where(function($q) use ($authId, $receiverId) {
                    $q->where('sender_id', $authId)->where('receiver_id', $receiverId);
                })
                ->orWhere(function($q) use ($authId, $receiverId) {
                    $q->where('sender_id', $receiverId)->where('receiver_id', $authId);
                })
                ->orderBy('created_at', 'asc')
                ->get();

            Message::where('sender_id', $receiverId)
                   ->where('receiver_id', $authId)
                   ->where('is_read', false)
                   ->update(['is_read' => true]);
        }

        return view('chat.show', compact('user', 'messages', 'contacts'));
    }

    /**
     * إرسال رسالة وبثها لحظياً (Real-time).
     */
    public function sendMessage(Request $request, User $user)
    {
        // 1. فحص أمني للمدخلات
        $request->validate([
            'message' => 'nullable|string|max:5000',
            'file'    => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf,doc,docx,mp4,zip|max:20480',
            'audio'   => 'nullable|file|mimes:mp3,wav,ogg,m4a,webm|max:10240',
        ]);

        $type = 'text';
        $filePath = null;

        // 2. حماية XSS: تنظيف المحتوى النصي
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
                $filePath = $file->store('chat_files/' . date('Y/m'), 'public');
            } elseif ($request->hasFile('audio')) {
                $type = 'audio';
                $filePath = $request->file('audio')->store('chat_audio/' . date('Y/m'), 'public');
            }
        } catch (Exception $e) {
            Log::error('Security Alert - File Upload Failure: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'خطأ أمني في رفع الملف'], 500);
        }

        if (empty($cleanMessage) && !$filePath) {
            return response()->json(['success' => false, 'error' => 'محتوى الرسالة غير صالح'], 400);
        }

        // 3. الحفظ باستخدام Eloquent
        $messageInstance = Message::create([
            'sender_id'   => (int) auth()->id(),
            'receiver_id' => (int) $user->id,
            'message'     => $cleanMessage,
            'type'        => $type,
            'file_path'   => $filePath,
        ]);

        // 4. البث اللحظي (The Real-time Magic)
        // تم التعديل هنا: وضع البث داخل try-catch لمنع توقف الكود إذا فشل الاتصال بسيرفر السوكيت
        try {
            broadcast(new MessageSent($messageInstance))->toOthers();
        } catch (Exception $e) {
            Log::error('Broadcasting failed, but message was saved: ' . $e->getMessage());
        }

        // 5. الرد لمرسل الرسالة (AJAX)
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message_data' => [
                    'id'          => $messageInstance->id,
                    'sender_id'   => (int) auth()->id(),
                    'user_name'   => auth()->user()->name,
                    'content'     => $cleanMessage,
                    'type'        => $type,
                    'file_path'   => $filePath ? asset('storage/' . $filePath) : null,
                    'created_at'  => $messageInstance->created_at->format('H:i A'),
                ]
            ]);
        }

        return back();
    }

    /**
     * دالة مساعدة لتأمين محتوى الإشعارات (اختياري لو احتجتها في مكان آخر).
     */
    private function getSecureNotifContent(string $type, ?string $message): string
    {
        if ($type === 'text') {
            return e(Str::limit($message, 50));
        }

        $mediaMap = [
            'image' => 'أرسل صورة 📷',
            'video' => 'أرسل فيديو 🎥',
            'audio' => 'رسالة صوتية 🎤',
            'file'  => 'أرسل ملفاً 📁',
        ];

        return $mediaMap[$type] ?? 'رسالة جديدة';
    }
}
