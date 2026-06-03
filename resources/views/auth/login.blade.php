<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - HospitalC</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card {
            box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center px-4">
    <div class="max-w-md w-full">
        <!-- Card -->
        <div class="bg-white rounded-3xl card p-8">
            <!-- Logo / Header -->
            <div class="text-center mb-8">
                <div class="mx-auto w-16 h-16 bg-blue-600 text-white rounded-2xl flex items-center justify-center text-3xl font-bold mb-4">
                    🏥
                </div>
                <h1 class="text-3xl font-bold text-gray-800">Chào mừng trở lại</h1>
                <p class="text-gray-500 mt-2">Đăng nhập để đặt lịch khám</p>
            </div>

            <!-- Form -->
            <form action="{{ route('login.post') }}" method="POST">
                @csrf

                @if ($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-2xl mb-6 text-sm">
                        {{ $errors->first() }}
                    </div>
                @endif

                <!-- Email -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input 
                        type="email" 
                        name="email" 
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                        placeholder="example@email.com"
                    >
                </div>

                <!-- Password -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Mật khẩu</label>
                    <input 
                        type="password" 
                        name="password" 
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                        placeholder="••••••••"
                    >
                </div>

                <!-- Submit Button -->
                <button 
                    type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3.5 rounded-2xl transition duration-200 flex items-center justify-center gap-2">
                    <span>Đăng nhập</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7-7 7" />
                    </svg>
                </button>
            </form>

            <!-- Link to Register -->
            <div class="text-center mt-6">
                <p class="text-gray-600">
                    Chưa có tài khoản? 
                    <a href="{{ route('register') }}" class="text-blue-600 hover:text-blue-700 font-medium">Đăng ký ngay</a>
                </p>
            </div>
        </div>

        <!-- Footer -->
        <p class="text-center text-gray-400 text-xs mt-6">
            © 2026 HospitalC
        </p>
    </div>

    <script>
        // Tự động focus vào email khi load trang
        document.querySelector('input[name="email"]').focus();
    </script>
    @include('components.back-to-previous')
</body>
</html>
