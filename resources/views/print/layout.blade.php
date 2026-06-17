<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Print Document')</title>
    <link rel="stylesheet" href="{{ asset('assets/css/print.css') }}">
    @stack('styles')
</head>
<body>
    <button onclick="window.print()" class="print-button no-print">Print Document</button>

    <div class="print-header">
        @include('print.header')
    </div>

    <div class="print-content">
        @yield('content')
    </div>

    <div class="print-footer">
        @include('print.footer')
    </div>

    @stack('scripts')
</body>
</html>
