<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titulo', 'Mis listas') · Listas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background: #fff; color: #292929; }
        .container { max-width: 1100px; }
        h1 { font-family: Georgia, 'Times New Roman', serif; font-size: 2rem; }
        .panel { padding: 1.5rem; border: 1px solid #dee2e6; border-radius: .375rem; }
        .login { max-width: 420px; margin: 2rem auto; }
        .token { color: #495057; overflow-wrap: anywhere; }
        .vacio { padding: 2rem 0; text-align: center; }
        .editor { max-width: 760px; margin: auto; }
        .numero-item { display: inline-flex; min-width: 1.5rem; color: #6c757d; }
    </style>
</head>
<body>
    <nav class="navbar border-bottom py-3" aria-label="Navegación principal">
        <div class="container gap-3">
            <a class="navbar-brand fw-bold" href="{{ route('listas.index') }}">Listas</a>
            @auth
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <span class="text-secondary">{{ Auth::user()->nombre }}</span>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button class="btn btn-outline-dark btn-sm" type="submit">Cerrar sesión</button>
                    </form>
                </div>
            @endauth
        </div>
    </nav>
    <main class="container py-5">
        @if (session('exito'))
            <div class="alert alert-success" role="status">{{ session('exito') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <p class="fw-bold mb-1">Revisa lo siguiente:</p>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @yield('contenido')
    </main>
    @stack('scripts')
</body>
</html>
