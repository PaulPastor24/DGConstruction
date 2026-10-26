<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Worker face recognition</title>
    <style>body{margin:0;background:#f5f8f6;font-family:system-ui,sans-serif;color:#173c29}*{box-sizing:border-box}button,input,select{font:inherit}label{line-height:1.6}</style>
    @stack('styles')
    <link rel="stylesheet" href="{{ asset('css/numbers.css') }}?v={{ filemtime(public_path('css/numbers.css')) }}">
</head>
<body>@yield('content')</body>
</html>
