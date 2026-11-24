@extends('app')
@section('titulo', 'Mis listas')
@section('contenido')
    <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap mb-4">
        <div>
            <h1 class="mb-2">Mis listas</h1>
            <p class="text-secondary mb-0">Crea, organiza y actualiza tus elementos.</p>
        </div>
        <a href="{{ route('listas.create') }}" class="btn btn-success">+ Crear lista</a>
    </div>
    <section class="panel">
        @if ($data->isEmpty())
            <div class="vacio">
                <h2 class="h4">Tu primera lista está por comenzar</h2>
                <p class="text-secondary">Agrega un título y los elementos que quieres organizar.</p>
                <a class="btn btn-success" href="{{ route('listas.create') }}">Crear mi primera lista</a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <caption class="visually-hidden">Listas del usuario autenticado</caption>
                    <thead><tr><th scope="col">#</th><th scope="col">Título</th><th scope="col">Token</th><th scope="col" class="text-end">Acciones</th></tr></thead>
                    <tbody>
                        @foreach ($data as $lista)
                            <tr>
                                <th scope="row">{{ $loop->iteration }}</th>
                                <td class="fw-semibold">{{ $lista->titulo }}</td>
                                <td><code class="token">{{ $lista->token }}</code></td>
                                <td>
                                    <div class="d-flex justify-content-end flex-wrap gap-2">
                                        <a href="{{ route('visitantes.show', $lista->token) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-eye" aria-hidden="true"></i> Ver pública
                                        </a>
                                        <a href="https://wa.me/?text={{ urlencode('Mira mi lista "' . $lista->titulo . '": ' . route('visitantes.show', $lista->token)) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-success">
                                            <i class="bi bi-whatsapp" aria-hidden="true"></i> Compartir
                                        </a>
                                        <a href="{{ route('listas.edit', $lista) }}" class="btn btn-outline-dark btn-sm" aria-label="Editar {{ $lista->titulo }}">Editar</a>
                                        <form action="{{ route('listas.destroy', $lista) }}" method="POST" onsubmit="return confirm('¿Eliminar esta lista y todos sus elementos? Esta acción no se puede deshacer.');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-outline-danger btn-sm" type="submit" aria-label="Eliminar {{ $lista->titulo }}">Eliminar</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
