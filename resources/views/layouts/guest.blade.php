<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title> {{ isset($pageTitle) ? $pageTitle : "Page" }} - {{ config('app.name', 'Bisuccy') }}</title>
    @vite('resources/js/app.js')
</head>

<body class="antialiased bg-gray-50">
    <main>
        <section class="relative flex flex-col items-center w-full h-full min-h-screen">
            <div class="container m-auto px-4 h-full">
                {{ $slot }}
            </div>
        </section>
    </main>
    <script>
        window.appModule = {
            currentLocale: '{{ App::currentLocale() }}',
            adminPrefix: '{{ config('app.admin-route-prefix') }}',
        };
    </script>
</body>

</html>