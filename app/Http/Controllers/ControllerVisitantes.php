<?php

namespace App\Http\Controllers;

use App\Models\Lista;
use Illuminate\Http\Request;

class ControllerVisitantes extends Controller
{
    public function show(string $token)
    {
        $lista = Lista::with(['usuario', 'items'])
            ->where('token', $token)
            ->firstOrFail();

        return view('visitantes.show', compact('lista'));
    }

    public function update(Request $request, string $token, int $id_item)
    {
        $lista = Lista::where('token', $token)->firstOrFail();

        $item = $lista->items()->where('id_item', $id_item)->firstOrFail();
        $item->completado = $request->boolean('completado');
        $item->save();

        $total = $lista->items()->count();
        $hechos = $lista->items()->where('completado', true)->count();

        return response()->json([
            'completado' => $item->completado,
            'total' => $total,
            'hechos' => $hechos,
            'porcentaje' => $total > 0 ? round($hechos * 100 / $total) : 0,
        ]);
    }
}