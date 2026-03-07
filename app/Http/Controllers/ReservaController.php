<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use Illuminate\Http\Request;

class ReservaController extends Controller
{
    public function index()
    {
        return response()->json(Reserva::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'area'  => 'required|string|max:100',
            'fecha' => 'required|date',
        ]);

        $reserva = Reserva::create([
            'area'   => $validated['area'],
            'fecha'  => $validated['fecha'],
            'estado' => 'Pendiente',
        ]);

        return response()->json(['reserva' => $reserva], 201);
    }

    public function update(Request $request, $id)
    {
        $reserva = Reserva::findOrFail($id);

        $validated = $request->validate([
            'area'  => 'required|string|max:100',
            'fecha' => 'required|date',
        ]);

        $reserva->update($validated);

        return response()->json(['reserva' => $reserva]);
    }

    public function destroy($id)
    {
        $reserva = Reserva::findOrFail($id);
        $reserva->delete();

        return response()->json(['ok' => true, 'id' => $id]);
    }
}