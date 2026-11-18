<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Movimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class MovimientoController extends Controller
{
    /**
     * Listado de movimientos del usuario autenticado, con filtros ya previstos
     * en el frontend (búsqueda, tipo y mes) y datos para los formularios.
     */
    public function index(Request $request)
    {
        $idUsuario = Auth::id();

        $consulta = Movimiento::query()
            ->where('id_usuario', $idUsuario)
            ->with('categoria');

        if ($request->filled('buscar')) {
            $consulta->where('descripcion', 'like', '%'.$request->string('buscar')->toString().'%');
        }

        if ($request->filled('tipo') && in_array($request->tipo, ['ingreso', 'gasto'], true)) {
            $consulta->where('tipo', $request->tipo);
        }

        if ($request->filled('mes') && preg_match('/^\d{4}-\d{2}$/', $request->mes)) {
            $inicio = $request->mes.'-01';
            $fin = date('Y-m-t', strtotime($inicio));
            $consulta->whereBetween('fecha', [$inicio, $fin]);
        }

        $movimientos = $consulta
            ->orderByDesc('fecha')
            ->orderByDesc('id_movimiento')
            ->get();

        $categoriasTodas = Categoria::query()
            ->where('id_usuario', $idUsuario)
            ->orderBy('activa', 'desc')
            ->orderBy('tipo_movimiento')
            ->orderBy('nombre')
            ->get();

        // Los selectores de nuevos movimientos solo usan categorías activas.
        $categorias = $categoriasTodas->where('activa', true)->values();

        $movimientoEditar = $request->filled('editar')
            ? Movimiento::query()->where('id_usuario', $idUsuario)->find($request->editar)
            : null;

        $categoriaEditar = $request->filled('editar_categoria')
            ? Categoria::query()->where('id_usuario', $idUsuario)->find($request->editar_categoria)
            : null;

        return view('pages.movimientos.index', compact(
            'movimientos',
            'categorias',
            'categoriasTodas',
            'movimientoEditar',
            'categoriaEditar'
        ) + [
            'tituloPagina' => 'Movimientos',
            'paginaActiva' => 'movimientos',
        ]);
    }

    public function store(Request $request)
    {
        $datos = $this->validar($request);
        $this->validarCategoria(Auth::id(), $datos['id_categoria'], $datos['tipo']);

        Movimiento::create([
            'id_usuario' => Auth::id(),
            'id_categoria' => $datos['id_categoria'],
            'monto' => $datos['monto'],
            'fecha' => $datos['fecha'],
            'tipo' => $datos['tipo'],
            'descripcion' => $datos['descripcion'],
            'metodo_pago' => $datos['metodo_pago'] ?? null,
            'notas' => $datos['notas'] ?? null,
        ]);

        return redirect()->route('movimientos')->with('success', 'Movimiento registrado correctamente.');
    }

    public function update(Request $request, int $id)
    {
        $movimiento = Movimiento::query()
            ->where('id_usuario', Auth::id())
            ->findOrFail($id);

        if ($movimiento->pagoDeuda()->exists()) {
            return redirect()->route('movimientos')->with('error',
                'El movimiento está asociado a un pago de deuda y no puede modificarse desde este módulo.');
        }

        $datos = $this->validar($request);
        $this->validarCategoria(Auth::id(), $datos['id_categoria'], $datos['tipo']);

        $movimiento->update([
            'id_categoria' => $datos['id_categoria'],
            'monto' => $datos['monto'],
            'fecha' => $datos['fecha'],
            'tipo' => $datos['tipo'],
            'descripcion' => $datos['descripcion'],
            'metodo_pago' => $datos['metodo_pago'] ?? null,
            'notas' => $datos['notas'] ?? null,
        ]);

        return redirect()->route('movimientos')->with('success', 'Movimiento actualizado correctamente.');
    }

    public function destroy(int $id)
    {
        $movimiento = Movimiento::query()
            ->where('id_usuario', Auth::id())
            ->findOrFail($id);

        // Integridad con la relación futura de pagos de deuda.
        if ($movimiento->pagoDeuda()->exists()) {
            return redirect()->route('movimientos')
                ->with('error', 'No se puede eliminar el movimiento porque está asociado a un pago de deuda.');
        }

        $movimiento->delete();

        return redirect()->route('movimientos')->with('success', 'Movimiento eliminado correctamente.');
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'tipo' => ['required', 'in:ingreso,gasto'],
            'id_categoria' => ['required', 'integer'],
            'monto' => ['required', 'numeric', 'gt:0', 'decimal:0,2', 'max:99999999.99'],
            'fecha' => ['required', 'date'],
            'descripcion' => ['required', 'string', 'max:150'],
            'metodo_pago' => ['nullable', 'string', 'max:50'],
            'notas' => ['nullable', 'string'],
        ], [
            'monto.gt' => 'El monto debe ser mayor que cero.',
            'fecha.date' => 'La fecha no es válida.',
        ]);
    }

    /**
     * La categoría debe existir, pertenecer al usuario, estar activa y
     * corresponder al tipo de movimiento seleccionado.
     */
    private function validarCategoria(int $idUsuario, int $idCategoria, string $tipo): void
    {
        $categoriaValida = Categoria::query()
            ->where('id_usuario', $idUsuario)
            ->where('id_categoria', $idCategoria)
            ->where('activa', true)
            ->where('tipo_movimiento', $tipo)
            ->exists();

        if (! $categoriaValida) {
            throw ValidationException::withMessages([
                'id_categoria' => 'La categoría seleccionada no es válida para este movimiento.',
            ]);
        }
    }
}
