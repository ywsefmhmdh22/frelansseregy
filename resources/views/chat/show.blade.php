@extends('layouts.master')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>

<style>
    :root {
        --chat-primary: #6366f1;
        --chat-secondary: #f3f4f6;
        --chat-bg: #f8fafc;
        --chat-sent: #6366f1;
        --chat-received: #ffffff;
    }

    body { background-color: #f0f2f5; }

    /* الحاوية الرئيسية */
    .chat-wrapper {
        height: 85vh;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 24px;
        overflow: hidden;
        border: 1px solid rgba(0,0,0,0.05);
    }

    /* قائمة المحادثات */
    .sidebar-contacts {
        border-left: 1px solid #edf2f7;
        background: #fff;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .contact-card {
        padding: 15px 20px;
        margin: 5px 12px;
        border-radius: 16px;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
        position: relative;
    }

    .contact-card:hover { background: #f8fafc; transform: translateY(-1px); }
    .contact-card.active { background: #eef2ff; }
    .contact-card.active h6 { color: var(--chat-primary); }

    /* منطقة الدردشة */
    .chat-main { background: #f8fafc; position: relative; }

    .chat-header {
        padding: 15px 25px;
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(8px);
        border-bottom: 1px solid rgba(0,0,0,0.05);
        z-index: 10;
    }

    .chat-messages {
        height: calc(85vh - 150px);
        overflow-y: auto;
        padding: 25px;
        display: flex;
        flex-direction: column;
        gap: 15px;
        scroll-behavior: smooth;
    }

    /* فقاعات الرسائل */
    .msg-bubble {
        max-width: 70%;
        padding: 12px 18px;
        font-size: 0.95rem;
        position: relative;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        line-height: 1.5;
    }

    .msg-sent {
        align-self: flex-end;
        background: var(--chat-sent);
        color: #fff;
        border-radius: 20px 20px 4px 20px;
    }

    .msg-received {
        align-self: flex-start;
        background: var(--chat-received);
        color: #1e293b;
        border-radius: 20px 20px 20px 4px;
        border: 1px solid #f1f5f9;
    }

    .msg-time {
        font-size: 0.7rem;
        opacity: 0.7;
        margin-top: 5px;
        display: block;
        text-align: left;
    }

    /* منطقة الإدخال العائمة */
    .input-wrapper {
        padding: 20px;
        background: #fff;
    }

    .floating-input {
        background: #f1f5f9;
        border-radius: 30px;
        padding: 8px 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .chat-input-field {
        border: none !important;
        background: transparent !important;
        box-shadow: none !important;
        padding: 10px 5px;
    }

    .action-btn {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: 0.3s;
        border: none;
        background: transparent;
        color: #64748b;
    }

    .action-btn:hover { background: #e2e8f0; color: var(--chat-primary); }
    .send-btn { background: var(--chat-primary); color: #fff; }
    .send-btn:hover { background: #4f46e5; transform: scale(1.05); }

    /* الصور والوسائط */
    .chat-media {
        max-width: 100%;
        border-radius: 12px;
        margin-bottom: 8px;
        cursor: pointer;
    }

    /* مخصص للـ Scrollbar */
    .chat-messages::-webkit-scrollbar { width: 5px; }
    .chat-messages::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
</style>

<div class="container py-5">
    <div class="chat-wrapper shadow-lg row g-0">

        <div class="col-md-4 sidebar-contacts d-none d-md-flex">
            <div class="p-4">
                <h4 class="fw-bold mb-3 text-dark">الرسائل</h4>
                <div class="position-relative">
                    <input type="text" class="form-control rounded-pill border-0 bg-light px-4 py-2" placeholder="بحث عن محادثة...">
                </div>
            </div>

            <div class="overflow-auto flex-grow-1">
                @forelse($contacts as $contact)
                    <a href="{{ route('messages.chat', $contact->id) }}" class="text-decoration-none d-block">
                        <div class="contact-card {{ isset($user) && $user->id == $contact->id ? 'active' : '' }}">
                            <div class="d-flex align-items-center gap-3">
                                <div class="position-relative">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($contact->name) }}&background=random" class="rounded-circle shadow-sm" width="50" alt="">
                                    <span class="position-absolute bottom-0 end-0 bg-success border border-white rounded-circle" style="width: 12px; height: 12px;"></span>
                                </div>
                                <div class="flex-grow-1 overflow-hidden">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="mb-0 fw-bold small text-truncate">{{ $contact->name }}</h6>
                                        <span class="text-muted" style="font-size: 0.7rem;">الآن</span>
                                    </div>
                                    <p class="mb-0 text-muted small text-truncate">اضغط لفتح المحادثة والبدء...</p>
                                </div>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="text-center p-5 text-muted">لا يوجد جهات اتصال</div>
                @endforelse
            </div>
        </div>

        <div class="col-md-8 col-12 chat-main d-flex flex-column">
            @if($user)
                <div class="chat-header d-flex align-items-center justify-content-between shadow-sm">
                    <div class="d-flex align-items-center gap-3">
                        <a href="{{ route('messages.chat') }}" class="d-md-none text-dark"><i class="fas fa-arrow-right"></i></a>
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=6366f1&color=fff" class="rounded-circle shadow-sm" width="45" alt="">
                        <div>
                            <h6 class="mb-0 fw-bold">{{ $user->name }}</h6>
                            <span class="text-success small"><i class="fas fa-circle me-1" style="font-size: 8px;"></i> متصل الآن</span>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="action-btn"><i class="fas fa-phone"></i></button>
                        <button class="action-btn"><i class="fas fa-video"></i></button>
                        <button class="action-btn"><i class="fas fa-ellipsis-v"></i></button>
                    </div>
                </div>

                <div class="chat-messages" id="chatWindow">
                    @foreach($messages as $msg)
                        <div class="msg-bubble animate__animated animate__fadeInUp animate__faster {{ $msg->sender_id == auth()->id() ? 'msg-sent' : 'msg-received' }}">
                            @if($msg->type == 'image')
                                <img src="{{ asset('storage/'.$msg->file_path) }}" class="chat-media shadow-sm" alt="">
                            @elseif($msg->type == 'audio')
                                <audio controls class="mb-1" style="width: 220px;"><source src="{{ asset('storage/'.$msg->file_path) }}" type="audio/mpeg"></audio>
                            @elseif($msg->type == 'video')
                                <video controls class="chat-media"><source src="{{ asset('storage/'.$msg->file_path) }}" type="video/mp4"></video>
                            @elseif($msg->type == 'file')
                                <div class="p-2 border rounded bg-light mb-1 text-dark">
                                    <a href="{{ asset('storage/'.$msg->file_path) }}" target="_blank" class="text-decoration-none">
                                        <i class="fas fa-file-pdf text-danger me-2"></i> ملف مرفق
                                    </a>
                                </div>
                            @endif

                            @if($msg->message) <div class="text-wrap">{{ $msg->message }}</div> @endif
                            <span class="msg-time">{{ $msg->created_at->format('H:i A') }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="input-wrapper border-top">
                    <form id="chatForm" action="{{ route('messages.send', $user->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="floating-input shadow-sm">
                            <button type="button" class="action-btn" id="emojiBtn"><i class="far fa-smile"></i></button>

                            <label for="fileInput" class="action-btn cursor-pointer">
                                <i class="fas fa-plus"></i>
                                <input type="file" name="file" id="fileInput" hidden>
                            </label>

                            <input type="text" name="message" id="messageInput" class="form-control chat-input-field flex-grow-1" placeholder="اكتب رسالتك هنا..." autocomplete="off">

                            <button type="button" class="action-btn" id="recordBtn"><i class="fas fa-microphone"></i></button>

                            <button type="submit" class="action-btn send-btn shadow-sm">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </form>
                </div>
            @else
                <div class="d-flex flex-column align-items-center justify-content-center h-100 bg-white">
                    <div class="bg-light p-5 rounded-circle mb-4 animate__animated animate__pulse animate__infinite">
                        <i class="fas fa-comments-alt fa-4x text-primary"></i>
                    </div>
                    <h4 class="fw-bold">مرحباً بك في نظام الشات</h4>
                    <p class="text-muted">اختر أحد جهات الاتصال على اليمين للبدء</p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    const authId = {{ auth()->id() }};
    const receiverId = {{ $user->id ?? 0 }};
    const notificationSound = new Audio('https://assets.mixkit.co/active_storage/sfx/2358/2358-preview.mp3');

    function scrollToBottom() {
        const chatWindow = document.getElementById("chatWindow");
        if(chatWindow) chatWindow.scrollTop = chatWindow.scrollHeight;
    }

    function appendMessage(data) {
        const chatWindow = document.getElementById("chatWindow");
        if(!chatWindow) return;

        const isSent = data.sender_id == authId;
        let contentHtml = '';

        if(data.type === 'image') contentHtml = `<img src="/storage/${data.file_path}" class="chat-media shadow-sm">`;
        else if(data.type === 'audio') contentHtml = `<audio controls style="width:220px"><source src="/storage/${data.file_path}"></audio>`;
        else if(data.type === 'video') contentHtml = `<video controls class="chat-media"><source src="/storage/${data.file_path}"></video>`;
        else if(data.type === 'file') contentHtml = `<div class="p-2 border rounded bg-light text-dark mb-1"><i class="fas fa-file me-2"></i>ملف جديد</div>`;

        const msgHtml = `
            <div class="msg-bubble animate__animated animate__fadeInUp animate__faster ${isSent ? 'msg-sent' : 'msg-received'}">
                ${contentHtml}
                ${data.message ? `<div class="text-wrap">${data.message}</div>` : ''}
                <span class="msg-time">الآن</span>
            </div>
        `;
        chatWindow.insertAdjacentHTML('beforeend', msgHtml);
        scrollToBottom();

        if(!isSent) notificationSound.play().catch(e => {});
    }

    async function handleChatSubmit() {
        const form = document.getElementById('chatForm');
        const input = document.getElementById('messageInput');
        if(!input.value.trim() && !document.getElementById('fileInput').files.length) return;

        const formData = new FormData(form);
        input.value = '';

        try {
            const response = await axios.post(form.action, formData);
            if(response.data.success) {
                appendMessage(response.data.message_data);
                form.reset();
            }
        } catch (error) {
            console.error("Error sending message", error);
        }
    }

    window.onload = function() {
        scrollToBottom();

        if (typeof Echo !== 'undefined') {
            window.Echo.private(`chat.${authId}`)
                .listen('.new-message', (e) => {
                    if(e.data.sender_id == receiverId) appendMessage(e.data);
                    else notificationSound.play().catch(e => {});
                });
        }

        document.getElementById('chatForm')?.addEventListener('submit', (e) => {
            e.preventDefault();
            handleChatSubmit();
        });

        document.getElementById('fileInput')?.addEventListener('change', () => handleChatSubmit());
    };
</script>
@endsection
