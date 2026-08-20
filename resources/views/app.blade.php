<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title inertia>{{ config('app.name', 'Hand Seal') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="icon" href="/icons/favicon.ico" />
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="manifest" href="/build/manifest.webmanifest">
    <meta name="theme-color" content="#0B1B2B">
    @routes
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @inertiaHead
</head>

<body class="font-sans antialiased">
@inertia
</body>
</html>
