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
     * عرض صفحة الدردشة والـ Inbox
     */
    public function chat(User $user = null)
    {
        $authId = auth()->id();

        // جلب قائمة الأشخاص (Inbox) مرتبين حسب أحدث رسالة
        // تم تعديل السطر أدناه لإصلاح خطأ SQLSTATE[HY093] عبر دمج المتغير مباشرة في الـ Raw Query
        $conversations = Message::where('sender_id', $authId)
            ->orWhere('receiver_id', $authId)
            ->select([
                DB::raw('MAX(created_at) as last_msg_at'),
                DB::raw("CASE WHEN sender_id = $authId THEN receiver_id ELSE sender_id END as contact_id")
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

            // تحديث حالة القراءة
            Message::where('sender_id', $receiverId)
                   ->where('receiver_id', $authId)
                   ->where('is_read', false)
                   ->update(['is_read' => true]);
        }

        return view('chat.show', compact('user', 'messages', 'contacts'));
    }

    /**
     * إرسال رسالة جديدة مع تنظيف المدخلات (XSS Protection)
     */
    public function sendMessage(Request $request, User $user)
    {
        // 1. تحسين الـ Validation
        $request->validate([
            'message' => 'nullable|string|max:5000', // تحديد حد أقصى للرسالة
            'file'    => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,mp4,zip|max:20480',
            'audio'   => 'nullable|file|mimes:mp3,wav,ogg,m4a|max:10240',
        ]);

        $type = 'text';
        $filePath = null;

        // --- حل مشكلة الـ XSS ---
        // تنظيف النص من أي وسوم HTML قد تكون خبيثة
        $cleanMessage = $request->message ? strip_tags($request->message) : null;

        // 2. معالجة الملفات
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
            return response()->json(['success' => false, 'error' => 'File upload failed'], 500);
        }

        // منع إرسال رسالة فارغة تماماً
        if (!$cleanMessage && !$filePath) {
            return response()->json(['success' => false, 'error' => 'Empty message'], 400);
        }

        // 3. حفظ الرسالة المنظفة في القاعدة
        $msg = Message::create([
            'sender_id'   => auth()->id(),
            'receiver_id' => $user->id,
            'message'     => $cleanMessage,
            'type'        => $type,
            'file_path'   => $filePath,
        ]);

        // تجهيز بيانات الإشعار
        $notifData = [
            'sender_id'   => auth()->id(),
            'user_name'   => auth()->user()->name,
            'content'     => $this->getNotifContent($type, $cleanMessage, $filePath),
            'receiver_id' => $user->id,
            'created_at'  => $msg->created_at->format('H:i A'),
            'type'        => $type,
            'file_path'   => $filePath ? asset('storage/' . $filePath) : null,
        ];

        // إرسال الحدث (Event)
        event(new \App\Events\NewMessageEvent($notifData));

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
    private function getNotifContent($type, $message, $path)
    {
        if ($type === 'text') return Str::limit($message, 50);
        if ($type === 'image') return 'أرسل صورة 📷';
        if ($type === 'video') return 'أرسل فيديو 🎥';
        if ($type === 'audio') return 'رسالة صوتية 🎤';
        return 'أرسل ملفاً 📁';
    }
}
