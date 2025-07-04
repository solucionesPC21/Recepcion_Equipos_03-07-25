<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Soluciones PC</title>
   <link rel="stylesheet" href="{{url('assets/css/bootstrap.min.css')}}">
   <link rel="stylesheet" href="{{url('assets/css/login/css.css')}}">
   <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">

</head>
<body>
@yield('navbar', View::make('layouts.partials.navbar'))
    <main class="form-container">
       @yield('content')
    </main>
<script src="{{url('assets/js/bootstrap.bundle.min.js')}}"></script>
</body>
</html>
