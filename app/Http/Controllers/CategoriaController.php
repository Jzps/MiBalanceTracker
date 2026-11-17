<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Movimiento;
use App\Models\Presupuesto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CategoriaController extends Controller
{
    public function store(Request $request)
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:80'],
            'tipo_movimiento' => ['required', 'in:ingreso,gasto'],
            'clasificacion_503020' => ['required', 'in:necesidad,deseo,ahorro,no_aplica'],
        ]);

        Categoria::create([
            'id_usuario' => Auth::id(),
            'nombre' => $datos['nombre'],
            'tipo_movimiento' => $datos['tipo_movimiento'],
            'clasificacion_503020' => $datos['clasificacion_503020'],
            'es_predeterminada' => false,
            'activa' => true,
        ]);

        return redirect()->route('movimientos')->with('success', 'Categoría creada correctamente.');
    }

    public function update(Request $request, int $id)
    {
        $categoria = Categoria::query()
            ->where('id_usuario', Auth::id())
            ->findOrFail($id);

        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:80'],
            'tipo_movimiento' => ['required', 'in:ingreso,gasto'],
            'clasificacion_503020' => ['required', 'in:necesidad,deseo,ahorro,no_aplica'],
        ]);

        // No se permite cambiar el tipo si ya existen relaciones incompatibles.
        if ($categoria->tipo_movimiento !== $datos['tipo_movimiento']) {
            $movimientosIncompatibles = Movimiento::query()
                ->where('id_categoria', $categoria->id_categoria)
                ->where('tipo', '!=', $datos['tipo_movimiento'])
                ->exists();

            $presupuestosIncompatibles = $datos['tipo_movimiento'] !== 'gasto'
                && Presupuesto::query()->where('id_categoria', $categoria->id_categoria)->exists();

            if ($movimientosIncompatibles || $presupuestosIncompatibles) {
                throw ValidationException::withMessages([
                    'tipo_movimiento' => 'No se puede cambiar el tipo: la categoría ya tiene registros relacionados.',
                ]);
            }
        }

        $categoria->nombre = $datos['nombre'];
        $categoria->tipo_movimiento = $datos['tipo_movimiento'];
        $categoria->clasificacion_503020 = $datos['clasificacion_503020'];

        // La restauración de categorías se realiza desde el botón "Activar".
        if ($request->has('activa')) {
            $categoria->activa = $request->boolean('activa');
        }

        $categoria->save();

        return redirect()->route('movimientos')->with('success', 'Categoría actualizada correctamente.');
    }

    /**
     * Eliminación lógica: conserva el historial de movimientos y presupuestos.
     */
    public function destroy(int $id)
    {
        $categoria = Categoria::query()
            ->where('id_usuario', Auth::id())
            ->findOrFail($id);

        $categoria->activa = false;
        $categoria->save();

        return redirect()->route('movimientos')
            ->with('success', 'Categoría desactivada. El historial se conserva sin cambios.');
    }
}
