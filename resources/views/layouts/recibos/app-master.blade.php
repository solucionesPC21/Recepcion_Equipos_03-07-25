<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Soluciones PC</title>
   <link rel="stylesheet" href="{{url('assets/css/bootstrap.min.css')}}">
   <link rel="stylesheet" href="{{url('assets/css/confirmacionModal/css.css')}}">
   <link rel="stylesheet" href="{{url('assets/css/nota/css.css')}}">
   <link rel="stylesheet" href="{{url('assets/css/registroclienteEquipo/css.css')}}">
   <link rel="stylesheet" href="{{url('assets/css/recibo/css.css')}}">
   <link rel="stylesheet" href="{{url('assets/css/paginado/css.css')}}">
   <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">

   
</head>
<body>
    @include('layouts.partials.navbar')
    <main class="container">
       @yield('content')
    </main>
    <br>
<br>
<br>


<script src="{{url('assets/js/jquery-3.7.1.min.js')}}"></script>
<script src="{{url('assets/js/bootstrap.bundle.min.js')}}"></script>
<script src="{{url('assets/js/nota/js.js')}}"></script>
<script src="{{url('assets/js/reparacion/js.js')}}"></script>


 
</body>
</html>
 