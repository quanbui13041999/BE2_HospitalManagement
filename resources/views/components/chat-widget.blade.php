{{-- Chat Widget - Messenger Style Dark Theme --}}
<div id="chat-widget" style="position:fixed; bottom:24px; right:24px; z-index:9999; font-family:'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">

    {{-- Nút mở/đóng chat (Messenger Bubble) --}}
    <button id="chat-toggle-btn" onclick="toggleChat()"
        style="width:60px;height:60px;border-radius:50%;background:linear-gradient(135deg, #0084ff, #00c6ff);
               color:#fff;border:none;cursor:pointer;box-shadow:0 4px 12px rgba(0,0,0,0.3);
               display:flex;align-items:center;justify-content:center;position:relative;transition:transform 0.2s;">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="white">
            <path d="M12 2C6.477 2 2 6.145 2 11.258c0 2.903 1.46 5.498 3.738 7.211V22l3.293-1.808c.928.258 1.91.4 2.969.4 5.523 0 10-4.146 10-9.259C22 6.145 17.523 2 12 2z"/>
        </svg>
        <span id="chat-badge" style="display:none;position:absolute;top:0;right:0;
            background:#ef4444;color:#fff;border-radius:50%;width:20px;height:20px;
            font-size:11px;line-height:20px;text-align:center;font-weight:bold;border:2px solid #fff;">0</span>
    </button>

    {{-- Khung chat (Messenger Dark) --}}
    <div id="chat-box" style="display:none; width:360px; height:520px; background:#242526;
         border-radius:12px 12px 0 0; box-shadow:0 12px 28px rgba(0,0,0,0.4);
         position:absolute; bottom:72px; right:0;
         flex-direction:column; overflow:hidden; border:1px solid #3e4042;">

        {{-- Header --}}
        <div style="background:#242526; color:#fff; padding:10px 12px;
                    display:flex; align-items:center; justify-content:space-between; flex-shrink:0;
                    border-bottom:1px solid #3e4042;">
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="position:relative;">
                    <img src="https://ui-avatars.com/api/?name=CSKH&background=0D8ABC&color=fff" 
                         style="width:36px;height:36px;border-radius:50%;object-fit:cover;">
                    <div style="width:10px;height:10px;background:#31a24c;border-radius:50%;position:absolute;bottom:0;right:0;border:2px solid #242526;"></div>
                </div>
                <div style="overflow:hidden; max-width:180px;">
                    <div style="font-weight:600;font-size:15px;white-space:nowrap;text-overflow:ellipsis;" id="chat-target-name">Hỗ trợ trực tuyến</div>
                    <div style="font-size:12px;color:#b0b3b8;" id="chat-status-label">Đang hoạt động</div>
                </div>
            </div>
            <div style="display:flex; gap:12px; align-items:center;">
                <button class="chat-icon-btn" style="color:#0084ff;"><i class="bi bi-telephone-fill"></i></button>
                <button class="chat-icon-btn" style="color:#0084ff;"><i class="bi bi-camera-video-fill"></i></button>
                <button class="chat-icon-btn" onclick="toggleChat()"><i class="bi bi-dash-lg"></i></button>
                <button class="chat-icon-btn" onclick="toggleChat()" style="color:#0084ff;"><i class="bi bi-x-lg"></i></button>
            </div>
        </div>

        {{-- Danh sách tin nhắn --}}
        <div id="chat-messages"
             style="flex:1; overflow-y:auto; padding:16px 12px; display:flex; flex-direction:column; gap:4px;
                    background:#18191a;">
            
            {{-- Welcome Info --}}
            <div style="display:flex; flex-direction:column; align-items:center; margin-bottom:20px; margin-top:10px;">
                <img src="https://ui-avatars.com/api/?name=MediCore&background=0084ff&color=fff" 
                     style="width:60px;height:60px;border-radius:50%;margin-bottom:10px;">
                <div style="color:#fff; font-weight:700; font-size:17px;">MediCore Hospital</div>
                <div style="color:#b0b3b8; font-size:13px;">Chúng tôi đã tạo cuộc trò chuyện này</div>
                <div style="color:#b0b3b8; font-size:11px; margin-top:15px; text-align:center; padding:0 30px;">
                    🔒 Tin nhắn và cuộc gọi được bảo mật bằng tính năng mã hóa đầu cuối.
                </div>
            </div>

        </div>

        {{-- Thanh nhập liệu --}}
        <div style="padding:8px 12px 16px; background:#242526; flex-shrink:0; display:flex; flex-direction:column; gap:8px;">
            <div style="display:flex; gap:10px; align-items:center;">
                <div style="display:flex; gap:12px; color:#0084ff; font-size:20px;">
                    <i class="bi bi-plus-circle-fill"></i>
                    <i class="bi bi-image"></i>
                    <i class="bi bi-sticky"></i>
                    <i class="bi bi-filetype-gif"></i>
                </div>
                <div style="flex:1; background:#3a3b3c; border-radius:20px; display:flex; align-items:center; padding:6px 12px;">
                    <textarea id="chat-input" placeholder="Aa"
                        rows="1"
                        style="flex:1; border:none; background:transparent; color:#fff;
                               font-size:15px; resize:none; max-height:100px; outline:none; padding:4px 0;"
                        onkeydown="handleChatKey(event)"></textarea>
                    <i class="bi bi-emoji-smile" style="color:#0084ff; font-size:20px; cursor:pointer;"></i>
                </div>
                <button id="chat-send-btn" onclick="sendChatMessage()"
                    style="background:transparent;border:none;color:#0084ff;cursor:pointer;font-size:22px;padding:0;display:flex;">
                    <i class="bi bi-hand-thumbs-up-fill"></i>
                </button>
            </div>
        </div>
    </div>

    <style>
        .chat-icon-btn { background:transparent; border:none; color:#b0b3b8; font-size:16px; cursor:pointer; padding:4px; display:flex; align-items:center; justify-content:center; }
        .chat-icon-btn:hover { color:#fff; }
        #chat-messages::-webkit-scrollbar { width: 6px; }
        #chat-messages::-webkit-scrollbar-thumb { background: #3e4042; border-radius: 10px; }
        
        .msg-bubble {
            max-width: 75%;
            padding: 8px 12px;
            font-size: 15px;
            line-height: 1.4;
            word-break: break-word;
            position: relative;
        }
        .msg-left {
            background: #3e4042;
            color: #fff;
            border-radius: 18px;
            align-self: flex-start;
        }
        .msg-right {
            background: #0084ff;
            color: #fff;
            border-radius: 18px;
            align-self: flex-end;
        }
        .msg-time {
            font-size: 11px;
            color: #b0b3b8;
            margin-top: 2px;
            display: none;
        }
        .msg-group:hover .msg-time { display: block; }
    </style>
</div>

<script>
let chatRoomId = null;
let chatOpen = false;
let lastMessageId = 0;
let pollInterval = null;

// Theo dõi thay đổi input để đổi icon send sang like
document.getElementById('chat-input').addEventListener('input', function() {
    const btn = document.getElementById('chat-send-btn');
    if (this.value.trim().length > 0) {
        btn.innerHTML = '<i class="bi bi-send-fill"></i>';
    } else {
        btn.innerHTML = '<i class="bi bi-hand-thumbs-up-fill"></i>';
    }
    this.style.height = 'auto';
    this.style.height = (this.scrollHeight) + 'px';
});

function toggleChat() {
    chatOpen = !chatOpen;
    const box = document.getElementById('chat-box');
    box.style.display = chatOpen ? 'flex' : 'none';
    if (chatOpen) {
        initChat();
        document.getElementById('chat-badge').style.display = 'none';
        document.getElementById('chat-badge').textContent = '0';
    } else {
        clearInterval(pollInterval);
    }
}

async function initChat() {
    if (chatRoomId) {
        startPolling();
        return;
    }
    try {
        const res = await fetch('/chat/room', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            }
        });
        const data = await res.json();
        if (data.success) {
            chatRoomId = data.room_id;
            document.getElementById('chat-status-label').textContent = 'Đang hoạt động';
            await loadMessages();
            startPolling();
        }
    } catch (e) {
        document.getElementById('chat-status-label').textContent = 'Lỗi kết nối';
    }
}

async function loadMessages(afterId = 0) {
    if (!chatRoomId) return;
    try {
        const url = `/chat/messages/${chatRoomId}` + (afterId ? `?after_id=${afterId}` : '');
        const res = await fetch(url);
        const data = await res.json();
        if (data.success && data.messages.length > 0) {
            data.messages.forEach(appendMessage);
            lastMessageId = Math.max(lastMessageId, ...data.messages.map(m => m.message_id));
            scrollToBottom();
        }
    } catch (e) {}
}

function appendMessage(msg) {
    const container = document.getElementById('chat-messages');
    const group = document.createElement('div');
    group.className = 'msg-group';
    group.style.cssText = `display:flex; align-items:flex-end; gap:8px; margin-bottom:8px; ${msg.is_mine ? 'flex-direction:row-reverse;' : 'flex-direction:row;'}`;

    // Avatar cho người gửi (chỉ hiện cho đối phương)
    if (!msg.is_mine) {
        const avatar = document.createElement('img');
        avatar.src = msg.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(msg.sender_name)}&background=random`;
        avatar.style.cssText = 'width:28px; height:28px; border-radius:50%; object-fit:cover; flex-shrink:0;';
        group.appendChild(avatar);
    }

    const contentBox = document.createElement('div');
    contentBox.style.cssText = `display:flex; flex-direction:column; align-items:${msg.is_mine ? 'flex-end' : 'flex-start'}; max-width:75%;`;

    const bubble = document.createElement('div');
    bubble.className = `msg-bubble ${msg.is_mine ? 'msg-right' : 'msg-left'}`;
    if (msg.is_ai) bubble.style.background = '#4e4f50';

    bubble.innerHTML = escapeHtml(msg.message_text);

    const time = document.createElement('div');
    time.className = 'msg-time';
    time.textContent = msg.sent_at;

    contentBox.appendChild(bubble);
    contentBox.appendChild(time);
    group.appendChild(contentBox);
    container.appendChild(group);
}

async function sendChatMessage() {
    const input = document.getElementById('chat-input');
    const text = input.value.trim() || '👍';
    if (!chatRoomId) return;
    
    input.value = '';
    input.style.height = 'auto';
    document.getElementById('chat-send-btn').innerHTML = '<i class="bi bi-hand-thumbs-up-fill"></i>';

    try {
        await fetch('/chat/send', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ room_id: chatRoomId, message_text: text })
        });
        await loadMessages(lastMessageId);
    } catch (e) {}
}

function startPolling() {
    clearInterval(pollInterval);
    pollInterval = setInterval(async () => {
        if (!chatOpen) return;
        await loadMessages(lastMessageId);
    }, 3000);
}

function handleChatKey(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendChatMessage();
    }
}

function scrollToBottom() {
    const el = document.getElementById('chat-messages');
    el.scrollTop = el.scrollHeight;
}

function escapeHtml(text) {
    return text.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
               .replace(/\n/g,'<br>');
}
</script>
