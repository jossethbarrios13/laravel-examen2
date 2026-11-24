@extends('app')
@section('titulo', 'Iniciar sesión')
@section('contenido')
    <section class="panel login">
        <p class="etiqueta mb-4">Un lugar para tus pendientes</p>
        <h1 class="titulo-login mb-4">Todo empieza<br>con una lista.</h1>
        <p class="text-secondary mb-4">Organiza tus ideas y prepara tus listas con todos los elementos que necesitas.</p>
        <a class="btn btn-success btn-lg w-100" href="{{ route('google') }}">Continuar con Google</a>
        <p class="small text-secondary mt-3 mb-0">Si es tu primera visita, crearemos tu cuenta automáticamente.</p>
    </section>
@endsection
