 @extends('layouts.master')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/picmo@5.8.5/dist/umd/index.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>

<style>
    .chat-inbox { height: 600px; overflow-y: auto; background: white; border-left: 1px solid #f1f1f1; }
    .chat-container { height: 500px; overflow-y: auto; background: #efe7dd url('https://user-images.githubusercontent.com/15075759/28719144-86dc0f70-73b1-11e7-911d-60d70fcded21.png'); padding: 20px; display: flex; flex-direction: column; }
    .contact-item { cursor: pointer; transition: 0.2s; border-bottom: 1px solid #f8f9fa; text-decoration: none; color: inherit; }
    .contact-item:hover { background: #f5f5f5; }
    .contact-item.active { background: #e9edef; border-right: 4px solid #00a884; }
    .message { max-width: 65%; margin-bottom: 10px; padding: 8px 12px; border-radius: 8px; font-size: 0.95rem; box-shadow: 0 1px 0.5px rgba(0,0,0,0.13); position: relative; word-wrap: break-word; }
    .sent { background: #d9fdd3; color: #111b21; align-self: flex-end; border-top-right-radius: 0; }
    .received { background: #ffffff; color: #111b21; align-self: flex-start; border-top-left-radius: 0; }
    .chat-img { max-width: 250px; border-radius: 5px; cursor: pointer; display: block; margin-bottom: 5px; transition: 0.3s; }
    .chat-img:hover { opacity: 0.9; }
    .audio-player { min-width: 200px; height: 35px; }
    .time { font-size: 0.65rem; color: #667781; display: block; margin-top: 2px; text-align: left; }
    .input-area { background: #f0f2f5; padding: 10px 15px; position: relative; }
    .btn-icon { color: #54656f; font-size: 1.3rem; transition: 0.2s; border: none; background: none; outline: none; }
    .btn-icon:hover { color: #008069; }
    #recordingStatus { color: #ea0038; font-weight: bold; display: none; }
    .cursor-pointer { cursor: pointer; }
    #pickerContainer { position: absolute; bottom: 60px; left: 10px; z-index: 1000; display: none; box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
    .unread-badge { position: absolute; top: -5px; right: -5px; font-size: 0.7rem; padding: 3px 6px; }
</style>

<div class="container py-4">
    <div class="row g-0 shadow rounded-3 overflow-hidden border">
        {{-- القائمة الجانبية للمحادثات --}}
        <div class="col-md-4 chat-inbox d-none d-md-block">
            <div class="p-3 bg-white border-bottom"><h5 class="mb-0 fw-bold text-primary">المحادثات</h5></div>
            <div class="list-group list-group-flush">
                @forelse($contacts as $contact)
                    <a href="{{ route('messages.chat', $contact->id) }}"
                       class="list-group-item list-group-item-action contact-item p-3 border-0 {{ isset($user) && $user->id == $contact->id ? 'active' : '' }}">
                        <div class="d-flex align-items-center gap-3">
                            <div class="position-relative">
                                {{-- تم إضافة alt لصور جهات الاتصال --}}
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($contact->name) }}&background=random"
                                     class="rounded-circle"
                                     width="45"
                                     alt="صورة {{ $contact->name }}">

                                @php
                                    $unreadCount = \App\Models\Message::where('sender_id', $contact->id)
                                        ->where('receiver_id', auth()->id())
                                        ->where('is_read', 0)
                                        ->count();
                                @endphp

                                @if($unreadCount > 0)
                                    <span class="badge rounded-pill bg-danger unread-badge animate__animated animate__bounceIn">
                                        {{ $unreadCount }}
                                    </span>
                                @endif
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0 small fw-bold">{{ $contact->name }}</h6>
                                <p class="mb-0 extra-small text-muted text-truncate">اضغط للتحدث..</p>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="text-center p-5 text-muted small">لا توجد محادثات سابقة</div>
                @endforelse
            </div>
        </div>

        {{-- نافذة المحادثة النشطة --}}
        <div class="col-md-8 col-12 d-flex flex-column bg-white">
            @if($user)
                <div class="card-header bg-white py-2 px-3 d-flex align-items-center border-bottom">
                    <a href="{{ route('messages.chat') }}" class="d-md-none text-dark me-2"><i class="fas fa-arrow-right"></i></a>
                    {{-- تم إضافة alt لصورة المستخدم النشط --}}
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=007bff&color=fff"
                         class="rounded-circle"
                         width="40"
                         alt="صورة {{ $user->name }}">
                    <h6 class="mb-0 fw-bold ms-2">{{ $user->name }}</h6>
                </div>

                <div class="card-body chat-container" id="chatWindow">
                    @foreach($messages as $msg)
                        <div class="message {{ $msg->sender_id == auth()->id() ? 'sent' : 'received' }}">
                            @if($msg->type == 'image')
                                <a href="{{ asset('storage/'.$msg->file_path) }}" target="_blank">
                                    {{-- تم إضافة alt للصور داخل المحادثة --}}
                                    <img src="{{ asset('storage/'.$msg->file_path) }}"
                                         class="chat-img"
                                         alt="صورة مرسلة في المحادثة">
                                </a>
                            @elseif($msg->type == 'audio')
                                <audio controls class="audio-player mb-1">
                                    <source src="{{ asset('storage/'.$msg->file_path) }}" type="audio/mpeg">
                                    متصفحك لا يدعم تشغيل الصوت.
                                </audio>
                            @elseif($msg->type == 'video')
                                <video controls style="max-width: 100%; border-radius: 8px;">
                                    <source src="{{ asset('storage/'.$msg->file_path) }}" type="video/mp4">
                                </video>
                            @elseif($msg->type == 'file')
                                <div class="p-2 border rounded bg-light mb-1">
                                    <a href="{{ asset('storage/'.$msg->file_path) }}" target="_blank" class="text-decoration-none">
                                        <i class="fas fa-file-download me-2"></i> تحميل الملف المرفق
                                    </a>
                                </div>
                            @endif

                            @if($msg->message) <div class="text-break">{{ $msg->message }}</div> @endif
                            <span class="time">{{ $msg->created_at->format('H:i A') }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="input-area border-top">
                    <div id="pickerContainer"></div>
                    <form id="chatForm" action="{{ route('messages.send', $user->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn-icon" id="emojiBtn" aria-label="إضافة إيموجي"><i class="far fa-smile"></i></button>

                            <div class="dropdown">
                                <button type="button" class="btn-icon" data-bs-toggle="dropdown" aria-label="إرفاق ملف"><i class="fas fa-paperclip"></i></button>
                                <ul class="dropdown-menu shadow border-0">
                                    <li><label for="fileImage" class="dropdown-item cursor-pointer"><i class="fas fa-image text-primary me-2"></i> صور <input type="file" name="file" id="fileImage" accept="image/*" hidden></label></li>
                                    <li><label for="fileDoc" class="dropdown-item cursor-pointer"><i class="fas fa-file text-warning me-2"></i> ملفات <input type="file" name="file" id="fileDoc" hidden></label></li>
                                </ul>
                            </div>

                            <input type="text" name="message" id="messageInput" class="form-control rounded-pill border-0 shadow-sm px-4" placeholder="اكتب رسالتك..." autocomplete="off" aria-label="رسالة نصية">

                            <div id="recordingStatus" class="small px-2">جاري التسجيل... <span id="timer">00:00</span></div>
                            <button type="button" id="recordBtn" class="btn-icon" aria-label="تسجيل صوتي"><i class="fas fa-microphone" id="micIcon"></i></button>

                            <button type="submit" class="btn btn-primary rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px;" aria-label="إرسال">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </form>
                </div>
            @else
                <div class="d-flex flex-column align-items-center justify-content-center h-100 text-muted">
                    <i class="fas fa-comments fa-4x mb-3" aria-hidden="true"></i>
                    <h5>اختر محادثة لبدء الدردشة</h5>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    const authId = {{ auth()->id() }};
    const receiverId = {{ $user->id ?? 0 }};
    const notificationSound = new Audio('https://assets.mixkit.co/active_storage/sfx/2358/2358-preview.mp3');
    let picker = null;

    function scrollToBottom() {
        const chatWindow = document.getElementById("chatWindow");
        if(chatWindow) chatWindow.scrollTop = chatWindow.scrollHeight;
    }

    function appendMessage(data) {
        const chatWindow = document.getElementById("chatWindow");
        if(!chatWindow) return;

        const isSent = data.sender_id == authId;
        let fileHtml = '';

        if(data.type === 'image') {
            fileHtml = `<a href="/storage/${data.file_path}" target="_blank"><img src="/storage/${data.file_path}" class="chat-img" alt="صورة مرسلة"></a>`;
        } else if(data.type === 'audio') {
            fileHtml = `<audio controls class="audio-player mb-1"><source src="/storage/${data.file_path}" type="audio/mpeg"></audio>`;
        } else if(data.type === 'video') {
            fileHtml = `<video controls style="max-width: 100%; border-radius: 8px;"><source src="/storage/${data.file_path}" type="video/mp4"></video>`;
        } else if(data.type === 'file') {
            fileHtml = `<div class="p-2 border rounded bg-light mb-1"><a href="/storage/${data.file_path}" target="_blank"><i class="fas fa-file-download me-2"></i> ملف مرفق</a></div>`;
        }

        const msgHtml = `
            <div class="message ${isSent ? 'sent' : 'received'} animate__animated animate__fadeInUp animate__faster">
                ${fileHtml}
                ${data.message ? `<div class="text-break">${data.message}</div>` : ''}
                <span class="time">الآن</span>
            </div>
        `;
        chatWindow.insertAdjacentHTML('beforeend', msgHtml);
        scrollToBottom();

        if(!isSent) {
            notificationSound.play().catch(e => console.log("Audio play blocked"));
        }
    }

    async function handleChatSubmit() {
        const form = document.getElementById('chatForm');
        const input = document.getElementById('messageInput');
        const formData = new FormData(form);

        if(!input.value.trim() && !document.getElementById('fileImage').files.length && !document.getElementById('fileDoc').files.length) return;

        const originalPlaceholder = input.placeholder;
        input.value = '';
        input.placeholder = "جاري الإرسال...";

        try {
            const response = await axios.post(form.action, formData);
            if(response.data.success) {
                appendMessage(response.data.message_data);
                form.reset();
            }
        } catch (error) {
            alert("خطأ في الإرسال");
        } finally {
            input.placeholder = originalPlaceholder;
        }
    }

    window.onload = function() {
        scrollToBottom();

        if (typeof Echo !== 'undefined') {
            window.Echo.private(`chat.${authId}`)
                .listen('.new-message', (e) => {
                    if(e.data.sender_id == receiverId) {
                        appendMessage(e.data);
                    } else {
                        notificationSound.play().catch(e => {});
                    }
                });
        }

        const chatForm = document.getElementById('chatForm');
        if(chatForm) {
            chatForm.addEventListener('submit', function(e) {
                e.preventDefault();
                handleChatSubmit();
            });
        }

        document.getElementById('fileImage')?.addEventListener('change', () => handleChatSubmit());
        document.getElementById('fileDoc')?.addEventListener('change', () => handleChatSubmit());
    };
</script>
@endsection
