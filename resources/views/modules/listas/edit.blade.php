@extends('app')
@section('titulo', 'Editar lista')
@section('contenido')
    <div class="editor">
        <a class="link-dark" href="{{ route('listas.index') }}">← Mis listas</a>
        <p class="etiqueta mt-4 mb-2">Ajusta los detalles</p>
        <h1 class="mb-4">Editar lista</h1>
        <form class="panel" action="{{ route('listas.update', $lista) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label for="titulo" class="form-label fw-semibold">Título de la lista</label>
                <input id="titulo" name="titulo" type="text" class="form-control form-control-lg" value="{{ old('titulo', $lista->titulo) }}" maxlength="200" placeholder="Por ejemplo: Compras de la semana" required>
            </div>
            <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                <h2 class="h5 mb-0">Elementos</h2>
                <button id="agregar-item" type="button" class="btn btn-outline-success btn-sm">+ Agregar elemento</button>
            </div>
            <p class="text-secondary small" id="ayuda-items">Agrega al menos un elemento. Puedes quitar los que ya no necesites.</p>
            <div id="items" aria-describedby="ayuda-items">
                @foreach (old('items', $items) as $indice => $item)
                    <div class="fila-item d-flex align-items-center gap-2 mb-3">
                        <span class="numero-item" aria-hidden="true">{{ $loop->iteration }}.</span>
                        @if (!empty($item['id_item']))
                            <input type="hidden" name="items[{{ $indice }}][id_item]" value="{{ $item['id_item'] }}">
                        @endif
                        <input type="text" class="form-control" name="items[{{ $indice }}][descripcion]" value="{{ $item['descripcion'] ?? '' }}" maxlength="255" aria-label="Descripción del elemento {{ $loop->iteration }}" required>
                        <button type="button" class="quitar-item btn btn-outline-danger btn-sm" aria-label="Quitar elemento {{ $loop->iteration }}">Quitar</button>
                    </div>
                @endforeach
            </div>
            <p id="estado-items" class="visually-hidden" role="status" aria-live="polite"></p>
            <div class="d-flex gap-2 mt-4 pt-3 border-top">
                <button class="btn btn-success" type="submit">Guardar lista</button>
                <a class="btn btn-outline-dark" href="{{ route('listas.index') }}">Cancelar</a>
            </div>
            <template id="plantilla-item">
                <div class="fila-item d-flex align-items-center gap-2 mb-3">
                    <span class="numero-item" aria-hidden="true"></span>
                    <input type="text" class="form-control" maxlength="255" required>
                    <button type="button" class="quitar-item btn btn-outline-danger btn-sm">Quitar</button>
                </div>
            </template>
            @push('scripts')
                <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
                <script>
                    $(function () {
                        function ordenarItems() {
                            const filas = $('#items .fila-item');
                            filas.each(function (indice) {
                                $(this).find('.numero-item').text((indice + 1) + '.');
                                $(this).find('input[type="hidden"]').attr('name', 'items[' + indice + '][id_item]');
                                $(this).find('input[type="text"]')
                                    .attr('name', 'items[' + indice + '][descripcion]')
                                    .attr('aria-label', 'Descripción del elemento ' + (indice + 1));
                                $(this).find('.quitar-item')
                                    .attr('aria-label', 'Quitar elemento ' + (indice + 1))
                                    .prop('disabled', filas.length === 1);
                            });
                            $('#estado-items').text(filas.length + ' elementos en la lista.');
                        }
                        $('#agregar-item').on('click', function () {
                            const fila = document.getElementById('plantilla-item').content.cloneNode(true);
                            $('#items').append(fila);
                            ordenarItems();
                            $('#items .fila-item:last input[type="text"]').trigger('focus');
                        });
                        $('#items').on('click', '.quitar-item', function () {
                            if ($('#items .fila-item').length > 1) {
                                const fila = $(this).closest('.fila-item');
                                const destino = fila.next('.fila-item').length ? fila.next('.fila-item') : fila.prev('.fila-item');
                                fila.remove();
                                ordenarItems();
                                destino.find('input[type="text"]').trigger('focus');
                            }
                        });
                        ordenarItems();
                    });
                </script>
            @endpush
        </form>
    </div>
@endsection
