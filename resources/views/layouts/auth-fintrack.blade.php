<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>@yield('title','FinTrack')</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#EFF6D2] flex items-center justify-center">

<div class="w-full max-w-md px-6">
    @yield('content')
</div>

</body>
</html>
