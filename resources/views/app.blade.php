<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titulo', 'Mis listas') · Listas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root { --verde: #235e49; --papel: #f7f6f0; }
        body { background: var(--papel); color: #25352d; }
        .navbar { background: #fffef9; }
        .marca { display: inline-grid; place-items: center; width: 32px; height: 32px; margin-right: 8px; color: white; background: var(--verde); border-radius: 8px; }
        h1 { font-family: Georgia, 'Times New Roman', serif; letter-spacing: -.035em; font-size: clamp(2.2rem, 5vw, 3.4rem); }
        .etiqueta { font-size: .75rem; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; color: var(--verde); }
        .panel { padding: clamp(1.25rem, 4vw, 2.5rem); border: 1px solid #daddd2; border-radius: 14px; background: #fffef9; }
        .btn-success { --bs-btn-bg: var(--verde); --bs-btn-border-color: var(--verde); --bs-btn-hover-bg: #194734; --bs-btn-hover-border-color: #194734; }
        .form-control:focus { border-color: var(--verde); box-shadow: 0 0 0 .2rem #235e4920; }
        .login { max-width: 560px; margin: 5vh auto; }
        .titulo-login { font-size: clamp(2.8rem, 7vw, 4rem); line-height: 1.05; }
        .token { color: var(--verde); overflow-wrap: anywhere; }
        .table { --bs-table-bg: transparent; }
        .table > :not(caption) > * > * { padding: 1.15rem .8rem; }
        .vacio { padding: 4rem 1rem; text-align: center; }
        .editor { max-width: 800px; margin: auto; }
        .numero-item { display: inline-flex; align-items: center; min-width: 1.8rem; color: #67746a; font-variant-numeric: tabular-nums; }
        @media (prefers-reduced-motion: no-preference) { main { animation: aparecer .3s ease-out; } @keyframes aparecer { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } } }
    </style>
</head>
<body>
    <nav class="navbar border-bottom py-3" aria-label="Navegación principal">
        <div class="container gap-3">
            <a class="navbar-brand fw-bold" href="{{ route('listas.index') }}"><span class="marca">✓</span> Listas</a>
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
