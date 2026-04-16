<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form action="{{ route('login.post') }}" method="POST">
    @csrf
    <h2>Đăng nhập</h2>

    @if($errors->any())
        <div style="color: red;">{{ $errors->first() }}</div>
    @endif

    <div>
        <label>Email:</label>
        <input type="email" name="email" required>
    </div>
    <div>
        <label>Mật khẩu:</label>
        <input type="password" name="password" required>
    </div>

    <button type="submit">Đăng nhập</button>
</form>
</body>
</html>