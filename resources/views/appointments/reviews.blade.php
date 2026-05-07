{{--
reviews.blade.php
@include('appointments.reviews')
Được nhúng vào cuối trang appointments/index.blade.php
--}}

{{-- ═══════════════════════════════════════════════════════════
MODAL: TẠO / CHỈNH SỬA ĐÁNH GIÁ
═══════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="reviewModal">
    <div class="modal review-modal">
        {{-- Header bác sĩ --}}
        <div class="review-doctor-header">
            <div class="review-doctor-avatar" id="rm-avatar"></div>
            <div>
                <div class="review-doctor-name" id="rm-doctor-name"></div>
                <div class="review-doctor-dept" id="rm-dept"></div>
                <div class="review-doctor-date" id="rm-date"></div>
            </div>
        </div>

        <h3 id="rm-title">Đánh giá bác sĩ</h3>
        <p style="font-size:.82rem;color:var(--gray-500);margin-bottom:16px">
            Chia sẻ trải nghiệm của bạn để giúp người bệnh khác
        </p>

        {{-- Sao đánh giá --}}
        <div class="star-rating" id="rm-stars">
            <span class="star" data-v="1">★</span>
            <span class="star" data-v="2">★</span>
            <span class="star" data-v="3">★</span>
            <span class="star" data-v="4">★</span>
            <span class="star" data-v="5">★</span>
        </div>
        <div class="star-label" id="rm-star-label">Chọn số sao</div>

        {{-- Nhận xét --}}
        <textarea id="rm-comment" placeholder="Chia sẻ cảm nhận của bạn về bác sĩ, thái độ, chuyên môn... (tuỳ chọn)"
            maxlength="1000" rows="4"></textarea>
        <div style="text-align:right;font-size:.7rem;color:var(--gray-400);margin-top:-10px;margin-bottom:14px">
            <span id="rm-char-count">0</span>/1000
        </div>

        <div id="rm-error" class="review-error" style="display:none"></div>

        <div class="modal-btns">
            <button type="button" class="modal-cancel-btn" onclick="closeReviewModal()">Huỷ</button>
            <button type="button" class="modal-confirm-btn review-submit-btn" id="rm-submit" onclick="submitReview()">
                <span id="rm-submit-text">Gửi đánh giá</span>
                <span id="rm-submit-spin" style="display:none">⏳</span>
            </button>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
MODAL: XÁC NHẬN XÓA ĐÁNH GIÁ
═══════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="deleteReviewModal">
    <div class="modal" style="max-width:400px">
        <div class="modal-icon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2">
                <polyline points="3 6 5 6 21 6" />
                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
                <path d="M10 11v6" />
                <path d="M14 11v6" />
                <path d="M9 6V4h6v2" />
            </svg>
        </div>
        <h3>Xóa đánh giá?</h3>
        <p>Hành động này không thể hoàn tác.</p>
        <div class="modal-btns">
            <button type="button" class="modal-cancel-btn" onclick="closeDeleteReviewModal()">Không</button>
            <button type="button" class="modal-confirm-btn" id="delete-review-confirm-btn"
                onclick="confirmDeleteReview()">
                Xóa
            </button>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
MODAL: TRẢ LỜI BÌNH LUẬN (Bác sĩ / Admin)
═══════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="replyReviewModal">
    <div class="modal" style="max-width:480px;text-align:left">
        <h3 style="margin-bottom:6px">Phản hồi đánh giá</h3>

        {{-- Hiển thị đánh giá gốc --}}
        <div class="reply-original-review">
            <div class="reply-original-stars" id="rr-stars"></div>
            <div class="reply-original-comment" id="rr-comment"></div>
            <div style="font-size:.72rem;color:var(--gray-400);margin-top:4px" id="rr-user"></div>
        </div>

        <label style="font-size:.8rem;font-weight:600;color:var(--gray-700);display:block;margin-bottom:6px">
            Nội dung phản hồi
        </label>
        <textarea id="rr-reply" placeholder="Cảm ơn bạn đã phản hồi…" maxlength="1000" rows="4"
            style="width:100%;padding:12px 14px;background:var(--gray-50);border:1.5px solid var(--gray-200);border-radius:16px;font-family:'Inter',sans-serif;font-size:.82rem;resize:vertical;outline:none;transition:border-color .2s;"></textarea>
        <div style="text-align:right;font-size:.7rem;color:var(--gray-400);margin-bottom:14px">
            <span id="rr-char-count">0</span>/1000
        </div>

        <div id="rr-error" class="review-error" style="display:none"></div>

        <div class="modal-btns">
            <button type="button" class="modal-cancel-btn" onclick="closeReplyModal()">Huỷ</button>
            <button type="button" class="modal-confirm-btn"
                style="background:linear-gradient(105deg,var(--primary),#2065cf)" onclick="submitReply()">Lưu phản
                hồi</button>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
STYLE riêng cho review
═══════════════════════════════════════════════════════════ --}}
<style>
    .review-modal {
        max-width: 480px;
        text-align: center;
    }

    .review-doctor-header {
        display: flex;
        align-items: center;
        gap: 14px;
        background: var(--gray-50);
        border: 1px solid var(--gray-200);
        border-radius: 18px;
        padding: 14px 16px;
        margin-bottom: 20px;
        text-align: left;
    }

    .review-doctor-avatar {
        width: 48px;
        height: 48px;
        background: linear-gradient(145deg, var(--primary), #448af2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: .95rem;
        color: white;
        flex-shrink: 0;
    }

    .review-doctor-name {
        font-weight: 700;
        font-size: .9rem;
        color: var(--gray-800);
    }

    .review-doctor-dept {
        font-size: .72rem;
        color: var(--gray-400);
        margin-top: 2px;
    }

    .review-doctor-date {
        font-size: .72rem;
        color: var(--primary);
        margin-top: 2px;
        font-weight: 600;
    }

    /* Sao */
    .star-rating {
        display: flex;
        justify-content: center;
        gap: 8px;
        margin-bottom: 8px;
    }

    .star {
        font-size: 2.2rem;
        cursor: pointer;
        color: var(--gray-300);
        transition: color .15s, transform .1s;
        line-height: 1;
        user-select: none;
    }

    .star.active,
    .star.hover {
        color: #f59e0b;
        transform: scale(1.15);
    }

    .star-label {
        font-size: .78rem;
        font-weight: 600;
        color: var(--gray-500);
        margin-bottom: 18px;
    }

    /* Review textarea */
    #rm-comment {
        width: 100%;
        padding: 12px 14px;
        background: var(--gray-50);
        border: 1.5px solid var(--gray-200);
        border-radius: 16px;
        font-family: 'Inter', sans-serif;
        font-size: .82rem;
        resize: vertical;
        outline: none;
        transition: border-color .2s;
    }

    #rm-comment:focus {
        border-color: var(--primary);
        background: var(--white);
    }

    .review-error {
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: 12px;
        padding: 10px 14px;
        font-size: .78rem;
        color: #dc2626;
        margin-bottom: 14px;
        text-align: left;
    }

    .review-submit-btn {
        background: linear-gradient(105deg, var(--primary), #2065cf) !important;
    }

    /* Badge đã đánh giá */
    .badge-reviewed {
        font-size: .72rem;
        font-weight: 700;
        color: #10b981;
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        padding: 5px 12px;
        border-radius: 30px;
    }

    /* Reply original review display */
    .reply-original-review {
        background: var(--gray-50);
        border: 1px solid var(--gray-200);
        border-radius: 14px;
        padding: 12px 14px;
        margin-bottom: 16px;
    }

    .reply-original-stars {
        color: #f59e0b;
        font-size: 1.1rem;
        margin-bottom: 4px;
    }

    .reply-original-comment {
        font-size: .82rem;
        color: var(--gray-700);
    }
</style>

{{-- ═══════════════════════════════════════════════════════════
JAVASCRIPT
═══════════════════════════════════════════════════════════ --}}
<script>
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    // ─── State ─────────────────────────────────────────────────
    let _reviewState = {
        appointmentId: null,
        doctorId: null,
        existingId: null,   // null = tạo mới, số = chỉnh sửa
        storeUrl: '',
        updateUrl: '',     // ví dụ: /reviews/{id}
        currentRating: 0,
    };

    const starLabels = ['', 'Tệ', 'Không tốt', 'Bình thường', 'Tốt', 'Xuất sắc'];

    // ─── Mở modal tạo / sửa ──────────────────────────────────
    function openReviewModal(opts) {
        _reviewState.appointmentId = opts.appointmentId;
        _reviewState.doctorId = opts.doctorId;
        _reviewState.storeUrl = opts.storeUrl;

        // Avatar: initials từ tên
        const words = opts.doctorName.trim().split(' ');
        const initials = words.length >= 2
            ? (words[words.length - 2][0] + words[words.length - 1][0]).toUpperCase()
            : opts.doctorName.trim().substring(0, 2).toUpperCase();
        document.getElementById('rm-avatar').textContent = initials;
        document.getElementById('rm-doctor-name').textContent = 'BS. ' + opts.doctorName;
        document.getElementById('rm-dept').textContent = opts.deptName;
        document.getElementById('rm-date').textContent = '🗓 ' + opts.workDate;

        if (opts.existing) {
            // Chỉnh sửa
            _reviewState.existingId = opts.existing.reviewId;
            _reviewState.updateUrl = opts.existing.updateUrl;
            _reviewState.currentRating = opts.existing.rating;
            document.getElementById('rm-title').textContent = 'Chỉnh sửa đánh giá';
            document.getElementById('rm-submit-text').textContent = 'Lưu thay đổi';
            document.getElementById('rm-comment').value = opts.existing.comment ?? '';
            document.getElementById('rm-char-count').textContent = (opts.existing.comment ?? '').length;
            setStars(opts.existing.rating);
        } else {
            // Tạo mới
            _reviewState.existingId = null;
            _reviewState.currentRating = 0;
            document.getElementById('rm-title').textContent = 'Đánh giá bác sĩ';
            document.getElementById('rm-submit-text').textContent = 'Gửi đánh giá';
            document.getElementById('rm-comment').value = '';
            document.getElementById('rm-char-count').textContent = '0';
            setStars(0);
        }

        document.getElementById('rm-error').style.display = 'none';
        document.getElementById('reviewModal').classList.add('active');
    }

    function closeReviewModal() {
        document.getElementById('reviewModal').classList.remove('active');
    }

    // ─── Sao ──────────────────────────────────────────────────
    function setStars(val) {
        _reviewState.currentRating = val;
        document.querySelectorAll('#rm-stars .star').forEach((s, i) => {
            s.classList.toggle('active', i < val);
        });
        document.getElementById('rm-star-label').textContent = starLabels[val] ?? 'Chọn số sao';
    }

    document.querySelectorAll('#rm-stars .star').forEach(star => {
        const v = +star.dataset.v;
        star.addEventListener('click', () => setStars(v));
        star.addEventListener('mouseenter', () => {
            document.querySelectorAll('#rm-stars .star').forEach((s, i) => s.classList.toggle('hover', i < v));
        });
        star.addEventListener('mouseleave', () => {
            document.querySelectorAll('#rm-stars .star').forEach(s => s.classList.remove('hover'));
        });
    });

    document.getElementById('rm-comment').addEventListener('input', function () {
        document.getElementById('rm-char-count').textContent = this.value.length;
    });

    // ─── Submit (tạo / sửa) ─────────────────────────────────
    async function submitReview() {
        const rating = _reviewState.currentRating;
        const comment = document.getElementById('rm-comment').value.trim();
        const errBox = document.getElementById('rm-error');

        if (!rating) {
            errBox.textContent = 'Vui lòng chọn số sao đánh giá.';
            errBox.style.display = 'block';
            return;
        }

        errBox.style.display = 'none';
        document.getElementById('rm-submit-text').style.display = 'none';
        document.getElementById('rm-submit-spin').style.display = 'inline';

        try {
            const isEdit = !!_reviewState.existingId;
            const url = isEdit ? _reviewState.updateUrl : _reviewState.storeUrl;

            const body = {
                _token: CSRF,
                rating: rating,
                comment: comment,
            };

            if (!isEdit) {
                body.appointment_id = _reviewState.appointmentId;
                body.doctor_id = _reviewState.doctorId;
            }

            if (isEdit) body._method = 'PUT';

            const res = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify(body),
            });
            const data = await res.json();

            if (data.success) {
                closeReviewModal();
                showToast('✅ ' + data.message);
                setTimeout(() => location.reload(), 1200);
            } else {
                errBox.textContent = data.message ?? 'Có lỗi xảy ra.';
                errBox.style.display = 'block';
            }
        } catch {
            errBox.textContent = 'Lỗi kết nối, vui lòng thử lại.';
            errBox.style.display = 'block';
        } finally {
            document.getElementById('rm-submit-text').style.display = 'inline';
            document.getElementById('rm-submit-spin').style.display = 'none';
        }
    }

    // ─── Xóa đánh giá ───────────────────────────────────────
    let _deleteReviewUrl = '';

    function openDeleteReviewModal(deleteUrl) {
        _deleteReviewUrl = deleteUrl;
        document.getElementById('deleteReviewModal').classList.add('active');
    }

    function closeDeleteReviewModal() {
        document.getElementById('deleteReviewModal').classList.remove('active');
    }

    async function confirmDeleteReview() {
        const btn = document.getElementById('delete-review-confirm-btn');
        btn.disabled = true;
        btn.textContent = '⏳';

        try {
            const res = await fetch(_deleteReviewUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ _method: 'DELETE', _token: CSRF }),
            });
            const data = await res.json();

            if (data.success) {
                closeDeleteReviewModal();
                showToast('🗑 ' + data.message);
                setTimeout(() => location.reload(), 1200);
            } else {
                alert(data.message ?? 'Có lỗi xảy ra.');
            }
        } catch {
            alert('Lỗi kết nối.');
        } finally {
            btn.disabled = false;
            btn.textContent = 'Xóa';
        }
    }

    // ─── Trả lời bình luận ──────────────────────────────────
    let _replyUrl = '';

    function openReplyModal(opts) {
        _replyUrl = opts.replyUrl;

        const starStr = '★'.repeat(opts.stars) + '☆'.repeat(5 - opts.stars);
        document.getElementById('rr-stars').textContent = starStr;
        document.getElementById('rr-comment').textContent = opts.comment ?? '(Không có nhận xét)';
        document.getElementById('rr-user').textContent = '— ' + opts.userName;
        document.getElementById('rr-reply').value = opts.existingReply ?? '';
        document.getElementById('rr-char-count').textContent = (opts.existingReply ?? '').length;
        document.getElementById('rr-error').style.display = 'none';

        document.getElementById('replyReviewModal').classList.add('active');
    }

    function closeReplyModal() {
        document.getElementById('replyReviewModal').classList.remove('active');
    }

    document.getElementById('rr-reply').addEventListener('input', function () {
        document.getElementById('rr-char-count').textContent = this.value.length;
    });

    async function submitReply() {
        const reply = document.getElementById('rr-reply').value.trim();
        const errBox = document.getElementById('rr-error');
        errBox.style.display = 'none';

        try {
            const res = await fetch(_replyUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ doctor_reply: reply, _token: CSRF }),
            });
            const data = await res.json();

            if (data.success) {
                closeReplyModal();
                showToast('💬 ' + data.message);
                setTimeout(() => location.reload(), 1200);
            } else {
                errBox.textContent = data.message ?? 'Có lỗi xảy ra.';
                errBox.style.display = 'block';
            }
        } catch {
            errBox.textContent = 'Lỗi kết nối.';
            errBox.style.display = 'block';
        }
    }

    // ─── Đóng modal khi click ngoài ──────────────────────────
    ['reviewModal', 'deleteReviewModal', 'replyReviewModal'].forEach(id => {
        document.getElementById(id).addEventListener('click', function (e) {
            if (e.target === this) this.classList.remove('active');
        });
    });

    // ─── Toast thông báo ─────────────────────────────────────
    function showToast(msg) {
        const t = document.createElement('div');
        t.textContent = msg;
        Object.assign(t.style, {
            position: 'fixed',
            bottom: '28px',
            right: '28px',
            background: '#1e2a3a',
            color: '#fff',
            padding: '12px 22px',
            borderRadius: '40px',
            fontSize: '.85rem',
            fontWeight: '600',
            zIndex: '9999',
            boxShadow: '0 8px 20px rgba(0,0,0,.2)',
            opacity: '0',
            transition: 'opacity .3s',
        });
        document.body.appendChild(t);
        requestAnimationFrame(() => t.style.opacity = '1');
        setTimeout(() => {
            t.style.opacity = '0';
            setTimeout(() => t.remove(), 400);
        }, 2800);
    }
</script>