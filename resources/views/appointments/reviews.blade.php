<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        .review-overlay {
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: rgba(0, 0, 0, .45);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }

        .review-overlay[hidden] {
            display: none;
        }

        .review-card {
            background: #fff;
            border-radius: 16px;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, .18);
            overflow: hidden;
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 20px 20px 16px;
            border-bottom: 1px solid #f0f0f0;
        }

        .review-doctor-info {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .review-avatar {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            object-fit: cover;
            background: #e8e8e8;
        }

        .review-doctor-name {
            font-size: 15px;
            font-weight: 600;
            margin: 0 0 2px;
            color: #1a1a1a;
        }

        .review-dept {
            font-size: 12px;
            color: #666;
            margin: 0;
        }

        .review-date {
            font-size: 12px;
            color: #999;
            margin: 4px 0 0;
        }

        .review-close {
            background: none;
            border: none;
            font-size: 18px;
            color: #999;
            cursor: pointer;
            padding: 4px 8px;
            line-height: 1;
            border-radius: 6px;
            transition: background .15s;
        }

        .review-close:hover {
            background: #f5f5f5;
            color: #333;
        }

        .review-body {
            padding: 20px 20px 0;
        }

        .review-label {
            font-size: 13px;
            font-weight: 500;
            color: #444;
            margin: 0 0 10px;
        }

        .star-row {
            display: flex;
            gap: 6px;
            margin-bottom: 8px;
        }

        .star-btn {
            font-size: 32px;
            background: none;
            border: none;
            cursor: pointer;
            color: #d0d0d0;
            padding: 0;
            line-height: 1;
            transition: color .1s, transform .1s;
        }

        .star-btn.active,
        .star-btn.hovered {
            color: #f59e0b;
            transform: scale(1.15);
        }

        .star-hint {
            font-size: 12px;
            color: #999;
            margin: 0 0 16px;
            min-height: 16px;
        }

        .review-textarea {
            width: 100%;
            box-sizing: border-box;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 10px 12px;
            font-size: 14px;
            line-height: 1.5;
            resize: vertical;
            font-family: inherit;
            color: #333;
            transition: border-color .15s;
        }

        .review-textarea:focus {
            outline: none;
            border-color: #6366f1;
        }

        .char-count {
            font-size: 12px;
            color: #bbb;
            text-align: right;
            margin: 4px 0 0;
        }

        .review-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding: 16px 20px;
            border-top: 1px solid #f0f0f0;
        }

        .btn-cancel {
            padding: 9px 20px;
            border-radius: 8px;
            font-size: 14px;
            background: #f5f5f5;
            border: none;
            color: #555;
            cursor: pointer;
            transition: background .15s;
        }

        .btn-cancel:hover {
            background: #ebebeb;
        }

        .btn-submit {
            padding: 9px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            background: #6366f1;
            color: #fff;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: background .15s, opacity .15s;
        }

        .btn-submit:disabled {
            opacity: .6;
            cursor: not-allowed;
        }

        .btn-submit:not(:disabled):hover {
            background: #4f46e5;
        }

        .spinner {
            width: 14px;
            height: 14px;
            border: 2px solid rgba(255, 255, 255, .4);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .7s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body>
    <div id="reviewModal" class="review-overlay" role="dialog" aria-modal="true" aria-labelledby="reviewModalTitle"
        hidden>
        <div class="review-card">

            {{-- Header --}}
            <div class="review-header">
                <div class="review-doctor-info">
                    <img id="rm-avatar" src="" alt="" class="review-avatar"
                        onerror="this.src='{{ asset('images/default-avatar.png') }}'">
                    <div>
                        <p id="reviewModalTitle" class="review-doctor-name"></p>
                        <p id="rm-dept" class="review-dept"></p>
                        <p id="rm-date" class="review-date"></p>
                    </div>
                </div>
                <button type="button" class="review-close" onclick="closeReviewModal()" aria-label="Đóng">✕</button>
            </div>

            {{-- Stars --}}
            <div class="review-body">
                <p class="review-label">Mức độ hài lòng</p>
                <div class="star-row" id="starRow" role="radiogroup" aria-label="Chọn số sao">
                    @for ($i = 1; $i <= 5; $i++)
                        <button type="button" class="star-btn" data-value="{{ $i }}" aria-label="{{ $i }} sao"
                            onclick="selectStar({{ $i }})">★</button>
                    @endfor
                </div>
                <p id="starHint" class="star-hint">Chạm vào sao để chọn</p>

                {{-- Comment --}}
                <label class="review-label" for="rm-comment">Nhận xét (tuỳ chọn)</label>
                <textarea id="rm-comment" class="review-textarea" rows="4" maxlength="1000"
                    placeholder="Chia sẻ trải nghiệm của bạn về bác sĩ và dịch vụ..."></textarea>
                <p class="char-count"><span id="charCount">0</span>/1000</p>
            </div>

            {{-- Footer --}}
            <div class="review-footer">
                <button type="button" class="btn-cancel" onclick="closeReviewModal()">Huỷ</button>
                <button type="button" class="btn-submit" id="btnSubmitReview" onclick="submitReview()">
                    <span id="btnSubmitText">Gửi đánh giá</span>
                    <span id="btnSubmitSpinner" class="spinner" hidden></span>
                </button>
            </div>
        </div>
    </div>
</body>

</html>