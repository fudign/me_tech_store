<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Сервис временно недоступен - Xiaomi Store</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8 text-center">
            <div class="mb-6">
                <svg class="mx-auto h-16 w-16 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-4">Сервис временно недоступен</h1>
            <p class="text-gray-600 mb-6">
                Извините, в данный момент мы проводим технические работы.
                Пожалуйста, попробуйте обновить страницу через несколько минут.
            </p>
            <div class="space-y-3">
                <button onclick="location.reload()" class="w-full bg-orange-600 text-white px-6 py-3 rounded-lg hover:bg-orange-700 transition">
                    Обновить страницу
                </button>
                <a href="/" class="block w-full bg-gray-100 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-200 transition">
                    Вернуться на главную
                </a>
            </div>
            <p class="text-sm text-gray-500 mt-6">
                Если проблема сохраняется, свяжитесь с нами:
                <a href="tel:+996XXXXXXXXX" class="text-orange-600 hover:underline">+996 XXX XXX XXX</a>
            </p>
        </div>
    </div>
</body>
</html>
