@extends('app')
@section('titulo', 'Iniciar sesión')
@section('contenido')
    <section class="panel login">
        <h1 class="mb-3">Iniciar sesión</h1>
        <p class="text-secondary mb-4">Ingresa con tu cuenta de Google para administrar tus listas.</p>
        <a class="btn btn-dark w-100" href="{{ route('google') }}">Continuar con Google</a>
        <p class="small text-secondary mt-3 mb-0">Si no tienes una cuenta, se registrará automáticamente al continuar con Google.</p>
    </section>
@endsection
