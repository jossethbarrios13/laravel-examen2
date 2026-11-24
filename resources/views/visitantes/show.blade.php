<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $lista->titulo }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

@php
    $total = $lista->items->count();
    $hechos = $lista->items->where('completado', true)->count();
    $porcentaje = $total > 0 ? round($hechos * 100 / $total) : 0;
@endphp

<div class="container py-5" style="max-width: 600px;">
    <div class="card shadow-sm">
        <div class="card-body">

            <h3 class="fw-bold">{{ $lista->titulo }}</h3>
            <p class="text-muted mb-2">Lista creada por: {{ $lista->usuario->nombre }}</p>
            <hr>

            <div class="d-flex justify-content-between">
                <strong id="contador">{{ $hechos }} de {{ $total }} completados</strong>
                <strong id="porcentaje">{{ $porcentaje }}%</strong>
            </div>

            <div class="progress my-2" style="height: 10px;">
                <div id="barra" class="progress-bar" role="progressbar"
                     style="width: {{ $porcentaje }}%"></div>
            </div>

            <div id="mensaje-exito"
                 class="alert alert-success mt-3 {{ ($total > 0 && $hechos === $total) ? '' : 'd-none' }}">
                <strong>¡Lo lograste!</strong> Has completado todos los elementos de tu lista
            </div>

            <ul class="list-group mt-3">
                @foreach ($lista->items as $item)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div class="form-check">
                            <input class="form-check-input item-check" type="checkbox"
                                   id="item-{{ $item->id_item }}"
                                   data-id="{{ $item->id_item }}"
                                   {{ $item->completado ? 'checked' : '' }}>
                            <label class="form-check-label {{ $item->completado ? 'text-decoration-line-through' : '' }}"
                                   for="item-{{ $item->id_item }}">
                                {{ $item->descripcion }}
                            </label>
                        </div>
                        <small class="text-muted estado">
                            {{ $item->completado ? 'Completado' : 'Pendiente' }}
                        </small>
                    </li>
                @endforeach
            </ul>

        </div>
    </div>
</div>

<script>
    const token = "{{ $lista->token }}";
    const csrf = document.querySelector('meta[name="csrf-token"]').content;

    document.querySelectorAll('.item-check').forEach(function (check) {
        check.addEventListener('change', function () {
            const idItem = check.dataset.id;
            const marcado = check.checked;

            fetch(`/${token}/items/${idItem}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify({ completado: marcado })
            })
            .then(function (respuesta) {
                if (!respuesta.ok) throw new Error('Error al guardar');
                return respuesta.json();
            })
            .then(function (data) {
                const fila = check.closest('li');
                fila.querySelector('label').classList.toggle('text-decoration-line-through', data.completado);
                fila.querySelector('.estado').textContent = data.completado ? 'Completado' : 'Pendiente';

                document.getElementById('contador').textContent = data.hechos + ' de ' + data.total + ' completados';
                document.getElementById('porcentaje').textContent = data.porcentaje + '%';
                document.getElementById('barra').style.width = data.porcentaje + '%';

                const todosListos = data.total > 0 && data.hechos === data.total;
                document.getElementById('mensaje-exito').classList.toggle('d-none', !todosListos);
            })
            .catch(function () {
                check.checked = !marcado;
                alert('No se pudo guardar el cambio. Intenta de nuevo.');
            });
        });
    });
</script>

</body>
</html>