<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form action="{{ route('register.post') }}" method="POST">
    @csrf
    <h2>Đăng ký tài khoản</h2>
    
    @if($errors->any())
        <div style="color: red;">{{ $errors->first() }}</div>
    @endif

    <div>
        <label>Họ và Tên:</label>
        <input type="text" name="full_name" required>
    </div>
    <div>
        <label>Email:</label>
        <input type="email" name="email" required>
    </div>
    <div>
        <label>Mật khẩu:</label>
        <input type="password" name="password" required>
    </div>
    <div>
        <label>Số điện thoại:</label>
        <input type="text" name="phone" required>
    </div>
    <div>
        <label>Địa chỉ:</label>
        <input type="text" name="address" required>
    </div>
    <div>
        <label>Ngày sinh:</label>
        <input type="date" name="date_of_birth">
    </div>
    <div>
        <label>Giới tính:</label>
        <select name="gender">
            <option value="Nam">Nam</option>
            <option value="Nữ">Nữ</option>
            <option value="Khác">Khác</option>
        </select>
    </div>

    <button type="submit">Đăng ký</button>
</form>
</body>
</html>