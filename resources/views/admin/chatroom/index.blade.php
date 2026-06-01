@extends('layouts.admin')
@section('title', 'Quản lý Chat CSKH')

@section('content')
<div style="display:flex; height:calc(100vh - 64px); font-family:sans-serif; background:#f1f5f9;">

    {{-- Sidebar: Danh sách phòng chat --}}
    <div id="rooms-sidebar" style="width:300px; background:#fff; border-right:1px solid #e2e8f0;
         display:flex; flex-direction:column; flex-shrink:0;">

        <div style="padding:16px; border-bottom:1px solid #e2e8f0; display:flex;
                    align-items:center; justify-content:space-between;">
            <h2 style="margin:0; font-size:16px; font-weight:700; color:#1e293b;">💬 Chat CSKH</h2>
            <span id="rooms-count" style="background:#dbeafe;color:#1d4ed8;
                border-radius:9999px;padding:2px 10px;font-size:12px;font-weight:600;">0</span>
        </div>

        <div style="padding:8px 12px; border-bottom:1px solid #e2e8f0;">
            <input type="text" id="search-rooms" placeholder="🔍 Tìm bệnh nhân..."
                style="width:100%;border:1px solid #cbd5e1;border-radius:8px;
                       padding:7px 10px;font-size:13px;box-sizing:border-box;outline:none;">
        </div>

        <div id="rooms-list" style="flex:1; overflow-y:auto; padding:4px 0;">
            <div style="text-align:center;padding:32px 16px;color:#94a3b8;font-size:13px;">
                Đang tải danh sách...
            </div>
        </div>
    </div>

    {{-- Khung chat chính --}}
    <div style="flex:1; display:flex; flex-direction:column; overflow:hidden;">

        {{-- Trạng thái chưa chọn phòng --}}
        <div id="no-room-selected" style="flex:1;display:flex;align-items:center;justify-content:center;
             flex-direction:column;gap:12px;color:#94a3b8;">
            <span style="font-size:48px;">💬</span>
            <p style="font-size:15px;margin:0;">Chọn một cuộc hội thoại để bắt đầu</p>
        </div>

        {{-- Khu vực chat khi đã chọn phòng --}}
        <div id="chat-area" style="display:none; flex-direction:column; height:100%;">

            {{-- Header phòng chat --}}
            <div id="chat-header" style="padding:14px 20px; background:#fff; border-bottom:1px solid #e2e8f0;
                 display:flex; align-items:center; justify-content:space-between; flex-shrink:0;">
                <div>
                    <div id="chat-patient-name" style="font-weight:700;font-size:15px;color:#1e293b;"></div>
                    <div id="chat-room-status" style="font-size:12px;color:#64748b;margin-top:2px;"></div>
                </div>
                <div style="display:flex; gap:8px;">
                    <button onclick="closeCurrentRoom()"
                        style="background:#fee2e2;color:#dc2626;border:none;border-radius:8px;
                               padding:7px 14px;cursor:pointer;font-size:13px;font-weight:600;">
                        ✕ Đóng phòng
                    </button>
                    <button onclick="deleteCurrentRoom()"
                        style="background:#f1f5f9;color:#64748b;border:none;border-radius:8px;
                               padding:7px 14px;cursor:pointer;font-size:13px;font-weight:600;"
                        title="Xóa vĩnh viễn phòng này">
                        🗑️ Xóa
                    </button>
                </div>
            </div>

            {{-- Tin nhắn --}}
            <div id="admin-messages"
                 style="flex:1; overflow-y:auto; padding:16px 20px;
                        display:flex; flex-direction:column; gap:10px; background:#f8fafc;">
            </div>

            {{-- Nhập liệu --}}
            <div style="padding:12px 16px; background:#fff; border-top:1px solid #e2e8f0;
                        display:flex; gap:10px; align-items:flex-end; flex-shrink:0;">
                <textarea id="admin-input" placeholder="Nhập tin nhắn trả lời bệnh nhân..."
                    rows="2"
                    style="flex:1;border:1px solid #cbd5e1;border-radius:10px;padding:10px 14px;
                           font-size:14px;resize:none;outline:none;max-height:100px;overflow-y:auto;"></textarea>
                <button onclick="adminSendMessage()"
                    style="background:#1d4ed8;color:#fff;border:none;border-radius:10px;
                           padding:10px 18px;cursor:pointer;font-size:14px;font-weight:600;
                           height:44px;white-space:nowrap;">
                    Gửi ➤
                </button>
            </div>
        </div>
    </div>
</div>
<style>
    .admin-msg-bubble:hover .del-msg-btn { display: flex; }
    .del-msg-btn {
        display: none; position: absolute; top: -8px; 
        width: 18px; height: 18px; background: #ef4444; color: #fff; 
        border-radius: 50%; align-items: center; justify-content: center;
        font-size: 10px; cursor: pointer; border: 2px solid #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    /* Sửa lại CSS động dựa trên vị trí bubble */
    .admin-msg-group[style*="align-items: flex-start"] .del-msg-btn { right: -8px; left: auto; }
    .admin-msg-group[style*="align-items: flex-end"] .del-msg-btn { left: -8px; right: auto; }
</style>

<script>
let currentRoomId = null;
let adminLastMsgId = 0;
let adminPollInterval = null;
let allRooms = [];

// Tải danh sách phòng
async function loadRooms() {
    try {
        const res = await fetch('/admin/chatroom/list');
        const data = await res.json();
        if (data.success) {
            allRooms = data.rooms;
            renderRooms(allRooms);
            document.getElementById('rooms-count').textContent = data.rooms.length;
        }
    } catch(e) {}
}

function renderRooms(rooms) {
    const container = document.getElementById('rooms-list');
    if (rooms.length === 0) {
        container.innerHTML = '<div style="text-align:center;padding:32px;color:#94a3b8;font-size:13px;">Chưa có cuộc hội thoại nào</div>';
        return;
    }
    container.innerHTML = rooms.map(room => `
        <div onclick="selectRoom(${room.room_id})"
             id="room-item-${room.room_id}"
             style="padding:12px 16px;cursor:pointer;border-bottom:1px solid #f1f5f9;
                    background:${room.room_id === currentRoomId ? '#eff6ff' : '#fff'};
                    transition:background 0.15s;"
             onmouseenter="this.style.background='#f8fafc'"
             onmouseleave="this.style.background='${room.room_id === currentRoomId ? '#eff6ff' : '#fff'}'">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px;">
                <span style="font-weight:600;font-size:13px;color:#1e293b;">
                    ${escapeHtml(room.patient_name)}
                </span>
                <div style="display:flex;align-items:center;gap:6px;">
                    ${room.unread_count > 0
                        ? `<span style="background:#ef4444;color:#fff;border-radius:9999px;
                                        padding:1px 8px;font-size:11px;font-weight:700;">${room.unread_count}</span>`
                        : ''}
                    <span style="font-size:10px;color:#94a3b8;">${room.last_time || ''}</span>
                </div>
            </div>
            <div style="font-size:12px;color:#64748b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:220px;">
                ${room.last_message ? escapeHtml(room.last_message) : 'Chưa có tin nhắn'}
            </div>
            <span style="font-size:10px;color:${room.status==='Mở' ? '#16a34a' : '#94a3b8'};
                         margin-top:4px;display:block;">● ${room.status}</span>
        </div>
    `).join('');
}

// Chọn phòng chat
async function selectRoom(roomId) {
    currentRoomId = roomId;
    adminLastMsgId = 0;
    clearInterval(adminPollInterval);

    document.getElementById('no-room-selected').style.display = 'none';
    const chatArea = document.getElementById('chat-area');
    chatArea.style.display = 'flex';
    document.getElementById('admin-messages').innerHTML = '';

    await loadAdminMessages();
    renderRooms(allRooms); // Cập nhật highlight

    // Polling
    adminPollInterval = setInterval(() => loadAdminMessages(true), 3000);
}

async function loadAdminMessages(incremental = false) {
    if (!currentRoomId) return;
    const url = `/admin/chatroom/${currentRoomId}/messages` + (incremental ? `?after_id=${adminLastMsgId}` : '');
    try {
        const res = await fetch(url);
        const data = await res.json();
        if (data.success) {
            if (!incremental) {
                document.getElementById('chat-patient-name').textContent = data.room.patient_name;
                document.getElementById('chat-room-status').textContent = 'Trạng thái: ' + data.room.status;
            }
            if (data.messages.length > 0) {
                data.messages.forEach(appendAdminMessage);
                adminLastMsgId = Math.max(adminLastMsgId, ...data.messages.map(m => m.message_id));
                scrollAdminToBottom();
            }
        }
    } catch(e) {}
}

function appendAdminMessage(msg) {
    const container = document.getElementById('admin-messages');
    const isPatient = msg.is_patient;
    const div = document.createElement('div');
    div.style.cssText = `display:flex;flex-direction:column;align-items:${isPatient ? 'flex-start' : 'flex-end'};
                          max-width:70%;${isPatient ? 'align-self:flex-start' : 'align-self:flex-end'}`;

    const senderLabel = isPatient
        ? `<span style="font-size:11px;color:#64748b;margin-bottom:2px;">👤 ${msg.sender_name}</span>`
        : (msg.is_ai
            ? `<span style="font-size:11px;color:#7c3aed;margin-bottom:2px;">🤖 AI Trợ Lý</span>`
            : `<span style="font-size:11px;color:#1d4ed8;margin-bottom:2px;">👨💼 CSKH: ${msg.sender_name}</span>`);

    div.innerHTML = `
        ${senderLabel}
        <div class="admin-msg-bubble" style="background:${isPatient ? '#fff' : (msg.is_ai ? '#7c3aed' : '#1d4ed8')};
                    color:${isPatient ? '#1e293b' : '#fff'};
                    padding:10px 14px; border-radius:12px; font-size:13px; line-height:1.5;
                    box-shadow:0 1px 4px rgba(0,0,0,0.08); word-break:break-word; position:relative;">
            ${escapeHtml(msg.message_text)}
            <button onclick="deleteAdminMessage(${msg.message_id}, this.closest('.admin-msg-group'))" 
                    class="del-msg-btn" title="Xóa tin nhắn">✕</button>
        </div>
        <span style="font-size:11px;color:#94a3b8;margin-top:3px;">${msg.sent_at}</span>
    `;
    div.className = 'admin-msg-group';
    container.appendChild(div);
}

async function adminSendMessage() {
    const input = document.getElementById('admin-input');
    const text = input.value.trim();
    if (!text || !currentRoomId) return;
    input.value = '';

    try {
        await fetch(`/admin/chatroom/${currentRoomId}/send`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ message_text: text })
        });
        await loadAdminMessages(true);
    } catch(e) {}
}

async function closeCurrentRoom() {
    if (!currentRoomId) return;
    if (window.appConfirm && !await window.appConfirm('Đóng phòng chat này?')) return;
    try {
        await fetch(`/admin/chatroom/${currentRoomId}/close`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        });
        document.getElementById('chat-room-status').textContent = 'Trạng thái: Đóng';
        clearInterval(adminPollInterval);
        await loadRooms();
    } catch(e) {}
}

async function deleteCurrentRoom() {
    if (!currentRoomId) return;
    if (window.appConfirm && !await window.appConfirm('Xác nhận xóa vĩnh viễn phòng chat này và toàn bộ tin nhắn?')) return;
    try {
        await fetch(`/admin/chatroom/${currentRoomId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        });
        currentRoomId = null;
        document.getElementById('chat-area').style.display = 'none';
        document.getElementById('no-room-selected').style.display = 'flex';
        await loadRooms();
    } catch(e) {}
}

async function deleteAdminMessage(msgId, element) {
    if (window.appConfirm && !await window.appConfirm('Xóa tin nhắn này?')) return;
    try {
        const res = await fetch(`/admin/chatroom/messages/${msgId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        });
        const data = await res.json();
        if (data.success) {
            element.remove();
        }
    } catch(e) {}
}

document.getElementById('search-rooms').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    const filtered = allRooms.filter(r => r.patient_name.toLowerCase().includes(q));
    renderRooms(filtered);
});

function scrollAdminToBottom() {
    const el = document.getElementById('admin-messages');
    el.scrollTop = el.scrollHeight;
}

function escapeHtml(text) {
    return String(text).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
                       .replace(/\n/g,'<br>');
}

// Khởi động
loadRooms();
setInterval(loadRooms, 10000); // Refresh danh sách mỗi 10 giây

document.getElementById('admin-input').addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        adminSendMessage();
    }
});
</script>
@endsection
