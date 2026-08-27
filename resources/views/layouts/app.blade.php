<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="نجيب — منصة تعليمية إلكترونية">

    <title>@yield('title', config('app.name', 'نجيب'))</title>

    @fonts

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-background text-text antialiased min-h-screen">
    @yield('content')
    <x-toast />
    @stack('scripts')
</body>
</html>
