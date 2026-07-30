<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    {{-- embed font --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    {{-- embed bootstrap css --}}
    <link href="{{ asset('client/css/bootstrap.min.css') }}" rel="stylesheet">
    {{-- embed common css --}}
    <link href="{{ asset('client/css/common.css') }}" rel="stylesheet">
    {{-- embed auth css --}}
    <link href="{{ asset('client/css/auth.css') }}" rel="stylesheet">
    <title>{{ config('app.name', 'Cupo') }} - Authentication</title>
</head>

<body>
    <main>
        @yield('content')
    </main>
    {{-- embed bootstrap js --}}
    <script src="{{ asset('client/js/bootstrap.bundle.min.js') }}"></script>
</body>

</html>
