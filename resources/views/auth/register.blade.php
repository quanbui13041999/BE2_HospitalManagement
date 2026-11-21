<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký tài khoản</title>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            font-family: 'Be Vietnam Pro', sans-serif;
        }

        .card {
            background: rgba(255, 255, 255, 0.04);
            border: 0.5px solid rgba(255, 255, 255, 0.12);
            border-radius: 20px;
            padding: 2.5rem;
            width: 100%;
            max-width: 480px;
            backdrop-filter: blur(20px);
        }

        .logo {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, #818cf8, #c084fc);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        .logo svg {
            width: 24px;
            height: 24px;
        }

        h2 {
            font-size: 22px;
            font-weight: 600;
            color: #f1f5f9;
            margin-bottom: 4px;
        }

        .sub {
            font-size: 13px;
            color: #94a3b8;
            margin-bottom: 2rem;
        }

        .row2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .field {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-bottom: 14px;
        }

        .field label {
            font-size: 12px;
            font-weight: 500;
            color: #94a3b8;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .field input,
        .field select {
            background: rgba(255, 255, 255, 0.06);
            border: 0.5px solid rgba(255, 255, 255, 0.12);
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 14px;
            color: #f1f5f9;
            font-family: 'Be Vietnam Pro', sans-serif;
            outline: none;
            transition: border-color 0.2s, background 0.2s;
            width: 100%;
            appearance: none;
        }

        .field input::placeholder {
            color: #475569;
        }

        .field input:focus,
        .field select:focus {
            border-color: rgba(129, 140, 248, 0.6);
            background: rgba(129, 140, 248, 0.08);
        }

        .field select option {
            background: #1e1b4b;
            color: #f1f5f9;
        }

        .sel-wrap {
            position: relative;
        }

        .sel-wrap::after {
            content: '';
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            width: 0;
            height: 0;
            border-left: 4px solid transparent;
            border-right: 4px solid transparent;
            border-top: 5px solid #94a3b8;
            pointer-events: none;
        }

        .error {
            background: rgba(239, 68, 68, 0.1);
            border: 0.5px solid rgba(239, 68, 68, 0.3);
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 13px;
            color: #fca5a5;
            margin-bottom: 16px;
        }

        .btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #818cf8, #c084fc);
            border: none;
            border-radius: 10px;
            color: #fff;
            font-size: 15px;
            font-weight: 600;
            font-family: 'Be Vietnam Pro', sans-serif;
            cursor: pointer;
            margin-top: 8px;
            letter-spacing: 0.01em;
            transition: opacity 0.2s, transform 0.1s;
        }

        .btn:hover {
            opacity: 0.9;
        }

        .btn:active {
            transform: scale(0.98);
        }

        .login-link {
            text-align: center;
            margin-top: 1.25rem;
            font-size: 13px;
            color: #64748b;
        }

        .login-link a {
            color: #818cf8;
            text-decoration: none;
            font-weight: 500;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .row2 {
                grid-template-columns: 1fr;
            }

            .card {
                padding: 1.75rem 1.5rem;
            }
        }
    </style>
</head>
<body>

<div class="card">
    <div class="logo">
        <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
            <circle cx="12" cy="7" r="4"/>
        </svg>
    </div>

    <h2>Tạo tài khoản</h2>
    <p class="sub">Chào mừng! Điền thông tin để bắt đầu.</p>

    <form action="{{ route('register.post') }}" method="POST">
        @csrf

        @if($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif

        <div class="row2">
            <div class="field">
                <label>Họ và Tên</label>
                <input type="text" name="full_name" placeholder="Nguyễn Văn A" value="{{ old('full_name') }}" required>
            </div>
            <div class="field">
                <label>Số điện thoại</label>
                <input type="text" name="phone" placeholder="0912 345 678" value="{{ old('phone') }}" required>
            </div>
        </div>

        <div class="field">
            <label>Email</label>
            <input type="email" name="email" placeholder="example@email.com" value="{{ old('email') }}" required>
        </div>

        <div class="field">
            <label>Mật khẩu</label>
            <input type="password" name="password" placeholder="Tối thiểu 8 ký tự" required>
        </div>

        <div class="field">
            <label>Địa chỉ</label>
            <input type="text" name="address" placeholder="123 Đường ABC, Quận 1, TP.HCM" value="{{ old('address') }}" required>
        </div>

        <div class="row2">
            <div class="field">
                <label>Ngày sinh</label>
                <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}">
            </div>
            <div class="field">
                <label>Giới tính</label>
                <div class="sel-wrap">
                    <select name="gender">
                        <option value="Nam" {{ old('gender') == 'Nam' ? 'selected' : '' }}>Nam</option>
                        <option value="Nữ" {{ old('gender') == 'Nữ' ? 'selected' : '' }}>Nữ</option>
                        <option value="Khác" {{ old('gender') == 'Khác' ? 'selected' : '' }}>Khác</option>
                    </select>
                </div>
            </div>
        </div>

        <button type="submit" class="btn">Đăng ký ngay</button>
    </form>

    <p class="login-link">Đã có tài khoản? <a href="{{ route('login') }}">Đăng nhập</a></p>
</div>

@include('components.back-to-previous')
</body>
</html>
