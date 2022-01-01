<!doctype html>
<html class="h-full font-sans antialiased js-focus-visible">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title . ' | StreamStats' }}</title>

        <link href="{{ asset('css/app.css') }}" rel="stylesheet">
        <link rel="icon" type="image/png" sizes="32x32" href="https://static.twitchcdn.net/assets/favicon-32-e29e246c157142c94346.png">
        <link rel="icon" type="image/png" sizes="16x16" href="https://static.twitchcdn.net/assets/favicon-16-52e571ffea063af7a7f4.png">
    </head>

    <body class="bg-slate-100 text-black h-full">
        {{ $slot }}

        <script src="{{ asset('js/app.js') }}"></script>
    </body>
</html>
