<?php

namespace App\Http\Controllers;

use App\Models\Lista;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ControllerListas extends Controller
{
    public function index()
    {
        $data = Auth::user()->listas()->orderByDesc('id_lista')->get();

        return view('modules.listas.index', compact('data'));
    }

    public function create()
    {
        $lista = new Lista();
        $items = [['descripcion' => '']];

        return view('modules.listas.create', compact('lista', 'items'));
    }

    public function store(Request $request)
    {
        $datos = $this->validar($request);

        DB::transaction(function () use ($datos) {
            do {
                $token = Str::random(20);
            } while (Lista::where('token', $token)->exists());

            $lista = new Lista();
            $lista->id_usuario = Auth::id();
            $lista->titulo = $datos['titulo'];
            $lista->token = $token;
            $lista->save();

            foreach ($datos['items'] as $item) {
                $lista->items()->create([
                    'descripcion' => $item['descripcion'],
                    'completado' => false,
                ]);
            }
        });

        return redirect()->route('listas.index')->with('exito', 'Lista creada correctamente.');
    }

    public function edit(Lista $lista)
    {
        $this->verificarPropietario($lista);
        $items = $lista->items()->orderBy('id_item')->get(['id_item', 'descripcion'])->toArray();

        return view('modules.listas.edit', compact('lista', 'items'));
    }

    public function update(Request $request, Lista $lista)
    {
        $this->verificarPropietario($lista);
        $datos = $this->validar($request, $lista);

        DB::transaction(function () use ($datos, $lista) {
            $lista->titulo = $datos['titulo'];
            $lista->save();

            $idsConservados = [];

            foreach ($datos['items'] as $datosItem) {
                if (! empty($datosItem['id_item'])) {
                    $item = $lista->items()->findOrFail($datosItem['id_item']);
                    $item->descripcion = $datosItem['descripcion'];
                    $item->save();
                } else {
                    $item = $lista->items()->create([
                        'descripcion' => $datosItem['descripcion'],
                        'completado' => false,
                    ]);
                }

                $idsConservados[] = $item->id_item;
            }

            $lista->items()->whereNotIn('id_item', $idsConservados)->delete();
        });

        return redirect()->route('listas.index')->with('exito', 'Lista actualizada correctamente.');
    }

    public function destroy(Lista $lista)
    {
        $this->verificarPropietario($lista);

        DB::transaction(function () use ($lista) {
            $lista->items()->delete();
            $lista->delete();
        });

        return redirect()->route('listas.index')->with('exito', 'Lista eliminada correctamente.');
    }

    private function verificarPropietario(Lista $lista): void
    {
        abort_unless((string) $lista->id_usuario === (string) Auth::id(), 403);
    }

    private function validar(Request $request, ?Lista $lista = null): array
    {
        return $request->validate([
            'titulo' => ['required', 'string', 'max:200'],
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['array:id_item,descripcion'],
            'items.*.descripcion' => ['required', 'string', 'max:255'],
            'items.*.id_item' => $lista
                ? ['nullable', 'integer', 'distinct', Rule::exists('items', 'id_item')->where('id_lista', $lista->id_lista)]
                : ['prohibited'],
        ], [
            'titulo.required' => 'Escribe un título para la lista.',
            'titulo.max' => 'El título admite hasta 200 caracteres.',
            'items.required' => 'Agrega al menos un elemento.',
            'items.min' => 'Agrega al menos un elemento.',
            'items.*.descripcion.required' => 'Cada elemento debe tener una descripción.',
            'items.*.descripcion.max' => 'Cada descripción admite hasta 255 caracteres.',
            'items.*.id_item.exists' => 'Uno de los elementos no pertenece a esta lista.',
            'items.*.id_item.distinct' => 'No puedes enviar el mismo elemento dos veces.',
        ]);
    }
}
