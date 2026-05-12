<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'iSpy World') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script src="https://unpkg.com/lucide@latest"></script>
        <script>lucide.createIcons();</script>
    </head>
    <body class="font-sans antialiased text-gray-900 bg-gray-50">
        
        <div class="flex min-h-screen w-full bg-background">
            
            @include('layouts.sidebar')

            <div class="flex flex-1 flex-col min-w-0">
                
                @include('layouts.header')

                <main class="flex-1 p-6 overflow-auto">
                    <div class="animate-fade-in">
                        {{ $slot }}
                    </div>
                </main>

            </div>
        </div>

    </body>
</html>