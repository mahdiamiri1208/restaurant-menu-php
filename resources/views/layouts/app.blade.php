<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#15110e">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>@yield('title', 'منوی پاریس لانژ | Paris Lounge')</title>
    <meta name="application-name" content="Paris Lounge">
    <meta name="description" content="منوی دیجیتال پاریس لانژ (Paris Lounge)">
    <meta property="og:site_name" content="Paris Lounge">
    <meta property="og:title" content="@yield('title', 'منوی پاریس لانژ | Paris Lounge')">
    <meta property="og:description" content="منوی دیجیتال پاریس لانژ (Paris Lounge)">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <style>
        @font-face {
            font-family: "Vazirmatn";
            src: url("{{ asset('fonts/vazirmatn.woff2') }}") format("woff2");
            font-style: normal;
            font-weight: 100 900;
            font-display: swap;
        }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    @yield('content')
</body>
</html>
