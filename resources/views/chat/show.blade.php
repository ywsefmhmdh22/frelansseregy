@extends('layouts.master')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

{{-- 🔥 تم تعليق هذا السطر لمنع ظهور خطأ الـ Vite Exception وتوقف الصفحة أثناء التست المحلي 🔥 --}}
{{-- @vite(['resources/js/app.js']) --}}

<style>
    :root {
        --glass-bg: rgba(255, 255, 255, 0.98);
        --primary-gradient: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        --chat-bg: #f3f4f6;
        --sent-msg: #6366f1;
        --recv-msg: #ffffff;
        --text-dark: #1f2937;
        --text-muted: #6b7280;
    }

    /* الحاوية الأساسية */
    .app-chat-container {
        display: flex;
        height: 80vh; /* ترك مساحة للناف بار العلوي والسفلي */
        width: 95%;
        max-width: 1400px;
        background: var(--glass-bg);
        backdrop-filter: blur(15px);
        margin: 20px auto;
        border-radius: 24px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.4);
        position: relative;
        z-index: 5;
    }

    .wa-sidebar {
        width: 350px;
        background: #fff;
        border-inline-end: 1px solid #f3f4f6;
        display: flex;
        flex-direction: column;
        z-index: 101;
        transition: all 0.3s ease;
    }

    .wa-header {
        padding: 24px;
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid #f9fafb;
    }

    .wa-avatar {
        width: 48px;
        height: 48px;
        border-radius: 16px;
        object-fit: cover;
    }

    .search-wrapper { padding: 15px 20px; }
    .search-input-group {
        background: #f9fafb;
        border-radius: 14px;
        padding: 12px 16px;
        display: flex;
        align-items: center;
        border: 1px solid #f3f4f6;
    }
    .search-input-group input {
        border: none; background: transparent; width: 100%; margin-inline-start: 10px; outline: none; font-size: 14px;
    }

    .contacts-scroll { flex: 1; overflow-y: auto; padding: 10px; }
    .contact-item {
        display: flex; padding: 14px; margin-bottom: 6px; border-radius: 18px; align-items: center; text-decoration: none !important; transition: all 0.2s ease;
        position: relative;
    }
    .contact-item:hover { background: #f9fafb; }
    .contact-item.active { background: #eef2ff; border: 1px solid #e0e7ff; }

    /* تحسين شكل العداد */
    .msg-badge {
        position: absolute;
        top: 15px;
        left: 15px;
        background: #ef4444;
        color: white;
        font-size: 11px;
        min-width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        font-weight: bold;
        box-shadow: 0 2px 5px rgba(239, 68, 68, 0.3);
    }
    .msg-badge.hidden { display: none; }

    .contact-details { margin-inline-start: 14px; flex: 1; }
    .contact-details h6 { margin: 0; font-weight: 600; color: var(--text-dark); font-size: 15px; }
    .contact-details p { margin: 2px 0 0; color: var(--text-muted); font-size: 12px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    .wa-chat-view { flex: 1; display: flex; flex-direction: column; background: var(--chat-bg); position: relative; }
    .chat-view-header {
        padding: 16px 24px; background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); display: flex; align-items: center; border-bottom: 1px solid #e5e7eb; z-index: 10;
    }

    .messages-area {
        flex: 1; overflow-y: auto; padding: 24px; display: flex; flex-direction: column; gap: 15px;
    }

    .bubble { max-width: 75%; padding: 12px 18px; font-size: 14px; position: relative; word-wrap: break-word; }
    .bubble-sent { align-self: flex-end; background: var(--primary-gradient); color: #fff; border-radius: 20px 20px 4px 20px; }
    .bubble-recv { align-self: flex-start; background: #fff; color: var(--text-dark); border-radius: 20px 20px 20px 4px; border: 1px solid #e5e7eb; }

    .media-container img { max-width: 100%; border-radius: 12px; margin-bottom: 5px; }
    .bubble-time { font-size: 10px; display: block; margin-top: 5px; opacity: 0.7; text-align: end; }

    .wa-footer {
        padding: 15px 25px; background: #fff; display: flex; align-items: center; gap: 12px; border-top: 1px solid #e5e7eb;
    }

    .input-field-container {
        flex: 1; background: #f3f4f6; border-radius: 20px; padding: 5px 15px; border: 1px solid #e5e7eb;
    }
    .wa-input { border: none; width: 100%; outline: none; padding: 10px 0; background: transparent; }

    .send-btn {
        width: 45px; height: 45px; border-radius: 50%; background: var(--primary-gradient); color: #fff; border: none;
        display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.3s;
    }
    .send-btn:hover { transform: scale(1.05); }

    /* التعديلات لضمان ظهور الناف بار والمحتوى بشكل صحيح */
    @media (max-width: 768px) {
        .app-chat-container {
            width: 100%;
            height: calc(100vh - 120px);
            margin: 0;
            border-radius: 0;
            position: relative;
        }

        .wa-sidebar {
            width: 100% !important;
            display: {{ isset($user) ? 'none' : 'flex' }};
        }

        .wa-chat-view {
            width: 100% !important;
            display: {{ isset($user) ? 'flex' : 'none' }};
        }
    }
</style>

<div class="app-chat-container" id="chatAppShell">
    <aside class="wa-sidebar" id="sideBar">
        <header class="wa-header">
            <h5 class="fw-bold mb-0">المحادثات</h5>
            <div class="bg-light p-2 rounded-circle"><i class="fas fa-plus text-primary"></i></div>
        </header>

        <div class="search-wrapper">
            <div class="search-input-group">
                <i class="fas fa-search text-muted"></i>
                <input type="text" placeholder="ابحث هنا...">
            </div>
        </div>

        <section class="contacts-scroll">
            @forelse($contacts as $contact)
                @php $unreadCount = $contact->unread_messages_count ?? 0; @endphp
                <a href="{{ route('messages.chat', $contact->id) }}"
                   id="contact-{{ $contact->id }}"
                   class="contact-item {{ isset($user) && $user->id == $contact->id ? 'active' : '' }}"
                   onclick="markAsRead(this, {{ $contact->id }})">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($contact->name) }}&background=6366f1&color=fff&bold=true" class="wa-avatar">
                    <div class="contact-details">
                        <div class="d-flex justify-content-between">
                            <h6>{{ $contact->name }}</h6>
                            <small class="text-muted">نشط</small>
                        </div>
                        <p class="last-msg-text">اضغط لبدء المحادثة</p>
                    </div>
                    <span class="msg-badge {{ $unreadCount == 0 ? 'hidden' : '' }}">{{ $unreadCount }}</span>
                </a>
            @empty
                <div class="text-center p-5 opacity-50">لا يوجد محادثات حالياً</div>
            @endforelse
        </section>
    </aside>

    <main class="wa-chat-view" id="chatView">
        @if(isset($user))
            <header class="chat-view-header">
                <button onclick="backToContacts(event)" class="btn d-md-none me-2 p-2 shadow-none">
                    <i class="fas fa-arrow-right text-primary fs-5"></i>
                </button>

                <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=6366f1&color=fff&bold=true" class="wa-avatar me-3" style="width:40px; height:40px;">
                <div class="flex-grow-1">
                    <h6 class="mb-0 fw-bold">{{ $user->name }}</h6>
                    <small class="text-success" style="font-size: 10px;"><i class="fas fa-circle"></i> متصل الآن</small>
                </div>
            </header>

            <div class="messages-area" id="chatWindow">
                @foreach($messages as $msg)
                    <div class="bubble {{ $msg->sender_id == auth()->id() ? 'bubble-sent' : 'bubble-recv' }} animate__animated animate__fadeIn" data-id="{{ $msg->id }}">
                        @if($msg->file_path)
                            <div class="media-container">
                                <img src="{{ asset('storage/'.$msg->file_path) }}" alt="attachment">
                            </div>
                        @endif
                        <div class="message-text">{{ $msg->message }}</div>
                        <span class="bubble-time">{{ $msg->created_at->format('h:i A') }}</span>
                    </div>
                @endforeach
            </div>

            <footer class="wa-footer" id="chatFooter">
                <label for="fileInput" class="mb-0 cursor-pointer">
                    <div class="bg-light p-2 rounded-circle text-muted"><i class="fas fa-paperclip"></i></div>
                </label>

                <form id="chatForm" action="{{ route('messages.send', $user->id) }}" method="POST" enctype="multipart/form-data" class="d-flex flex-grow-1 align-items-center gap-2">
                    @csrf
                    <input type="file" name="file" id="fileInput" hidden onchange="submitChat()">
                    <div class="input-field-container">
                        <input type="text" name="message" id="messageInput" class="wa-input" placeholder="اكتب رسالتك..." autocomplete="off">
                    </div>
                    <button type="submit" class="send-btn">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
            </footer>
        @else
            <div class="d-flex flex-column align-items-center justify-content-center h-100 text-center p-4">
                <div class="bg-white p-5 rounded-circle shadow-sm mb-4 animate__animated animate__pulse animate__infinite">
                    <i class="fas fa-comments fa-4x text-primary opacity-50"></i>
                </div>
                <h4 class="fw-bold">Maheer Chat Platform</h4>
                <p class="text-muted">اختر أحد جهات الاتصال للبدء</p>
            </div>
        @endif
    </main>
</div>

<script>
    const authId = {{ auth()->id() }};
    const receiverId = {{ $user->id ?? 0 }};

    function backToContacts(e) {
        if(e) e.preventDefault();
        window.location.replace("{{ route('messages.chat') }}");
    }

    function markAsRead(element, contactId) {
        const badge = element.querySelector('.msg-badge');
        if(badge) {
            badge.classList.add('hidden');
            badge.innerText = '0';
        }
    }

    // تعديل برمجي لمنع الانهيار إذا لم تكن مسارات الملفات مهيأة في الـ local
    function scrollToBottom() {
        const win = document.getElementById("chatWindow");
        if(win) win.scrollTop = win.scrollHeight;
    }

    function appendMessage(data) {
        const win = document.getElementById("chatWindow");
        if(!win) return;
        if (data.id && document.querySelector(`[data-id="${data.id}"]`)) return;

        const isSentByMe = data.sender_id == authId;
        const messageText = data.content || data.message;
        const createdAt = data.created_at || 'الآن';

        let mediaHtml = data.file_path ? `<div class="media-container"><img src="${data.file_path}"></div>` : '';

        const html = `
            <div class="bubble ${isSentByMe ? 'bubble-sent' : 'bubble-recv'} animate__animated animate__fadeInUp" data-id="${data.id || ''}">
                ${mediaHtml}
                <div class="message-text">${messageText || ''}</div>
                <span class="bubble-time">${createdAt}</span>
            </div>`;

        win.insertAdjacentHTML('beforeend', html);
        scrollToBottom();
    }

    async function submitChat() {
        const form = document.getElementById('chatForm');
        const input = document.getElementById('messageInput');
        const fileInput = document.getElementById('fileInput');

        if(!input.value.trim() && !fileInput.files.length) return;

        const formData = new FormData(form);
        const originalMsg = input.value;
        input.value = '';

        try {
            const res = await axios.post(form.action, formData);
            if(res.data.success) {
                appendMessage(res.data.message_data);
                fileInput.value = '';
            }
        } catch (e) {
            input.value = originalMsg;
            alert('حدث خطأ أثناء الإرسال');
        }
    }

    window.onload = () => {
        scrollToBottom();

        document.getElementById('chatForm')?.addEventListener('submit', (e) => {
            e.preventDefault();
            submitChat();
        });

        if (window.Echo) {
            window.Echo.private(`chat.${authId}`)
                .listen('.MessageSent', (e) => {
                    if(e.sender_id == receiverId || e.sender_id == authId) {
                        appendMessage(e);
                    } else {
                        const contactRow = document.getElementById(`contact-${e.sender_id}`);
                        if(contactRow) {
                            const badge = contactRow.querySelector('.msg-badge');
                            const lastMsg = contactRow.querySelector('.last-msg-text');

                            if(lastMsg) lastMsg.innerText = e.message || "أرسل ملفاً...";

                            if(badge) {
                                let currentCount = parseInt(badge.innerText) || 0;
                                badge.innerText = currentCount + 1;
                                badge.classList.remove('hidden');

                                contactRow.classList.add('animate__animated', 'animate__pulse');
                                setTimeout(() => contactRow.classList.remove('animate__pulse'), 1000);
                            }
                        }
                    }
                });
        }
    };
</script>
@endsection
