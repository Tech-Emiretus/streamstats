<!doctype html>
<html class="h-full font-sans antialiased js-focus-visible">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title . ' | StreamStats' }}</title>

        <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    </head>

    <body class="bg-slate-100 text-black h-full">
        {{ $slot }}

        <script src="{{ asset('js/app.js') }}"></script>
    </body>
</html>
