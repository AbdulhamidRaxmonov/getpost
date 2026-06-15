<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YesPOS - Kirish</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body style="background: linear-gradient(135deg, #0f2444 0%, #1e3a5f 50%, #0d4f8c 100%); min-height: 100vh;"
      class="flex items-center justify-center p-4">

    <!-- Animated background shapes -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-96 h-96 rounded-full opacity-10"
             style="background: radial-gradient(circle, #3b82f6, transparent)"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 rounded-full opacity-10"
             style="background: radial-gradient(circle, #1d4ed8, transparent)"></div>
    </div>

    <div class="w-full max-w-md relative">
        <!-- Card -->
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
            <!-- Header -->
            <div style="background: linear-gradient(135deg, #1e3a5f, #0d4f8c);" class="px-8 py-8 text-center">
                <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center mx-auto mb-4 backdrop-blur">
                    <i class="fas fa-cash-register text-white text-3xl"></i>
                </div>
                <h1 class="text-3xl font-bold text-white">YesPOS</h1>
                <p class="text-blue-200 mt-1 text-sm">Savdo boshqaruv tizimi</p>
            </div>

            <!-- Form -->
            <div class="px-8 py-8">
                <h2 class="text-xl font-bold text-gray-800 mb-6 text-center">Admin paneliga kirish</h2>

                <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-phone text-blue-500 mr-1"></i>
                            Telefon raqam yoki Email
                        </label>
                        <input type="text" name="phone" value="{{ old('phone') }}"
                               placeholder="+998901234567"
                               class="w-full px-4 py-3 rounded-xl border @error('phone') border-red-400 bg-red-50 @else border-gray-200 @enderror focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                        @error('phone')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-lock text-blue-500 mr-1"></i>
                            Parol
                        </label>
                        <div class="relative">
                            <input type="password" name="password" id="password"
                                   placeholder="••••••••"
                                   class="w-full px-4 py-3 rounded-xl border @error('password') border-red-400 bg-red-50 @else border-gray-200 @enderror focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition pr-12">
                            <button type="button" onclick="togglePassword()"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <i class="fas fa-eye" id="eye-icon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 text-sm text-gray-600">
                            <input type="checkbox" name="remember" class="rounded border-gray-300 text-blue-600">
                            Eslab qolish
                        </label>
                    </div>

                    <button type="submit"
                            class="w-full py-3 rounded-xl font-semibold text-white transition-all duration-200 hover:opacity-90 hover:shadow-lg active:scale-95"
                            style="background: linear-gradient(135deg, #1e3a5f, #0d4f8c);">
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        Kirish
                    </button>
                </form>

                <div class="mt-6 pt-6 border-t border-gray-100 text-center">
                    <p class="text-xs text-gray-400">
                        Demo: <strong>+998901234567</strong> / <strong>admin123</strong>
                    </p>
                </div>
            </div>
        </div>

        <p class="text-center text-blue-300 text-xs mt-4">
            © {{ date('Y') }} YesPOS. Barcha huquqlar himoyalangan.
        </p>
    </div>

    <script>
        function togglePassword() {
            const pwd = document.getElementById('password');
            const icon = document.getElementById('eye-icon');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                pwd.type = 'password';
                icon.className = 'fas fa-eye';
            }
        }
    </script>
</body>
</html>
