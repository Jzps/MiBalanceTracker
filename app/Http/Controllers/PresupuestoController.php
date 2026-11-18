<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Movimiento;
use App\Models\Presupuesto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class PresupuestoController extends Controller
{
    /**
     * Listado de presupuestos propios con progreso calculado desde movimientos reales.
     */
    public function index(Request $request)
    {
        $idUsuario = Auth::id();

        $presupuestoEditar = $request->filled('editar')
            ? Presupuesto::query()->where('id_usuario', $idUsuario)->find($request->editar)
            : null;

        $presupuestos = Presupuesto::query()
            ->where('id_usuario', $idUsuario)
            ->with('categoria')
            ->orderByDesc('fecha_inicio')
            ->get()
            ->map(function (Presupuesto $presupuesto) use ($idUsuario) {
                $gastado = (float) Movimiento::query()
                    ->where('id_usuario', $idUsuario)
                    ->where('id_categoria', $presupuesto->id_categoria)
                    ->where('tipo', 'gasto')
                    ->whereBetween('fecha', [
                        $presupuesto->fecha_inicio->toDateString(),
                        $presupuesto->fecha_fin->toDateString(),
                    ])
                    ->sum('monto');

                $limite = (float) $presupuesto->monto_limite;
                $porcentaje = round(($gastado / $limite) * 100, 2);

                if ($gastado > $limite) {
                    $estado = 'excedido';
                    $mensaje = 'Has superado el presupuesto de '.$presupuesto->categoria->nombre.'.';
                } elseif ($porcentaje >= (float) $presupuesto->porcentaje_alerta) {
                    $estado = 'preventivo';
                    $mensaje = 'Has utilizado el '.number_format($porcentaje, 0).'% de tu presupuesto.';
                } else {
                    $estado = 'normal';
                    $mensaje = '';
                }

                return [
                    'presupuesto' => $presupuesto,
                    'categoria' => $presupuesto->categoria,
                    'gastado' => $gastado,
                    'disponible' => $limite - $gastado,
                    'porcentaje' => $porcentaje,
                    'estado' => $estado,
                    'mensaje' => $mensaje,
                ];
            });

        $categorias = Categoria::query()
            ->where('id_usuario', $idUsuario)
            ->where('activa', true)
            ->where('tipo_movimiento', 'gasto')
            ->orderBy('nombre')
            ->get();

        return view('pages.presupuestos.index', compact('presupuestos', 'categorias', 'presupuestoEditar') + [
            'tituloPagina' => 'Presupuestos',
            'paginaActiva' => 'presupuestos',
        ]);
    }

    public function store(Request $request)
    {
        $datos = $this->validar($request);
        $this->validarCategoria(Auth::id(), $datos['id_categoria']);

        Presupuesto::create([
            'id_usuario' => Auth::id(),
            'id_categoria' => $datos['id_categoria'],
            'monto_limite' => $datos['monto_limite'],
            'fecha_inicio' => $datos['fecha_inicio'],
            'fecha_fin' => $datos['fecha_fin'],
            'porcentaje_alerta' => $datos['porcentaje_alerta'] ?? 80,
        ]);

        return redirect()->route('presupuestos')->with('success', 'Presupuesto creado correctamente.');
    }

    public function update(Request $request, int $id)
    {
        $presupuesto = Presupuesto::query()
            ->where('id_usuario', Auth::id())
            ->findOrFail($id);

        $datos = $this->validar($request);
        $this->validarCategoria(Auth::id(), $datos['id_categoria']);

        $presupuesto->update([
            'id_categoria' => $datos['id_categoria'],
            'monto_limite' => $datos['monto_limite'],
            'fecha_inicio' => $datos['fecha_inicio'],
            'fecha_fin' => $datos['fecha_fin'],
            'porcentaje_alerta' => $datos['porcentaje_alerta'] ?? 80,
        ]);

        return redirect()->route('presupuestos')->with('success', 'Presupuesto actualizado correctamente.');
    }

    public function destroy(int $id)
    {
        $presupuesto = Presupuesto::query()
            ->where('id_usuario', Auth::id())
            ->findOrFail($id);

        $presupuesto->delete();

        return redirect()->route('presupuestos')->with('success', 'Presupuesto eliminado correctamente.');
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'id_categoria' => ['required', 'integer'],
            'monto_limite' => ['required', 'numeric', 'gt:0', 'decimal:0,2', 'max:99999999.99'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'porcentaje_alerta' => ['nullable', 'numeric', 'gt:0', 'max:100', 'decimal:0,2'],
        ], [
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
            'monto_limite.gt' => 'El límite debe ser mayor que cero.',
        ]);
    }

    private function validarCategoria(int $idUsuario, int $idCategoria): void
    {
        $categoriaValida = Categoria::query()
            ->where('id_usuario', $idUsuario)
            ->where('id_categoria', $idCategoria)
            ->where('activa', true)
            ->where('tipo_movimiento', 'gasto')
            ->exists();

        if (! $categoriaValida) {
            throw ValidationException::withMessages([
                'id_categoria' => 'La categoría seleccionada no es válida para un presupuesto.',
            ]);
        }
    }
}
