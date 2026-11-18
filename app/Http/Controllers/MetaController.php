<?php

namespace App\Http\Controllers;

use App\Models\MetaAhorro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MetaController extends Controller
{
    public function index(Request $request)
    {
        $idUsuario = Auth::id();

        $metas = MetaAhorro::query()
            ->where('id_usuario', $idUsuario)
            ->with('aportes')
            ->orderByDesc('fecha_objetivo')
            ->get()
            ->map(function (MetaAhorro $meta) {
                $acumulado = (float) $meta->aportes->sum('monto');
                $objetivo = (float) $meta->monto_objetivo;

                return [
                    'meta' => $meta,
                    'acumulado' => $acumulado,
                    'pendiente' => max($objetivo - $acumulado, 0),
                    'progreso' => round(($acumulado / $objetivo) * 100, 2),
                    'anchoBarra' => min(($acumulado / $objetivo) * 100, 100),
                    'aportes' => $meta->aportes->sortByDesc('fecha'),
                ];
            });

        $metaEditar = $request->filled('editar')
            ? MetaAhorro::query()->where('id_usuario', $idUsuario)->find($request->editar)
            : null;

        return view('pages.metas.index', compact('metas', 'metaEditar') + [
            'tituloPagina' => 'Metas',
            'paginaActiva' => 'metas',
        ]);
    }

    public function store(Request $request)
    {
        $datos = $this->validar($request);

        MetaAhorro::create([
            'id_usuario' => Auth::id(),
            'nombre' => $datos['nombre'],
            'tipo_meta' => $datos['tipo_meta'],
            'monto_objetivo' => $datos['monto_objetivo'],
            'fecha_objetivo' => $datos['fecha_objetivo'],
            'estado' => 'activa',
        ]);

        return redirect()->route('metas')->with('success', 'Meta creada correctamente.');
    }

    public function update(Request $request, int $id)
    {
        $meta = MetaAhorro::query()
            ->where('id_usuario', Auth::id())
            ->findOrFail($id);

        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'tipo_meta' => ['required', 'in:meta_ahorro,fondo_emergencia'],
            'monto_objetivo' => ['required', 'numeric', 'gt:0', 'decimal:0,2', 'max:99999999.99'],
            'fecha_objetivo' => ['required', 'date'],
            'estado' => ['nullable', 'in:activa,completada,pausada,cancelada'],
        ], [
            'monto_objetivo.gt' => 'El monto objetivo debe ser mayor que cero.',
        ]);

        $acumulado = (float) $meta->aportes()->sum('monto');
        $estadoSolicitado = $datos['estado'] ?? $meta->estado;

        if ($estadoSolicitado === 'completada') {
            throw ValidationException::withMessages([
                'estado' => 'El estado "completada" se determina automáticamente con los aportes.',
            ]);
        }

        if ($estadoSolicitado === 'activa' && $acumulado >= (float) $datos['monto_objetivo']) {
            $estadoSolicitado = 'completada';
        }

        $meta->update([
            'nombre' => $datos['nombre'],
            'tipo_meta' => $datos['tipo_meta'],
            'monto_objetivo' => $datos['monto_objetivo'],
            'fecha_objetivo' => $datos['fecha_objetivo'],
            'estado' => $estadoSolicitado,
        ]);

        return redirect()->route('metas')->with('success', 'Meta actualizada correctamente.');
    }

    public function destroy(int $id)
    {
        $meta = MetaAhorro::query()
            ->where('id_usuario', Auth::id())
            ->findOrFail($id);

        if ($meta->aportes()->exists()) {
            return redirect()->route('metas')->with('error',
                'No se puede eliminar la meta porque tiene aportes registrados. Cancélala para conservar el historial.');
        }

        $meta->delete();

        return redirect()->route('metas')->with('success', 'Meta eliminada correctamente.');
    }

    public function storeAporte(Request $request, int $meta)
    {
        $metaAhorro = MetaAhorro::query()
            ->where('id_usuario', Auth::id())
            ->findOrFail($meta);

        if ($metaAhorro->estado !== 'activa') {
            return redirect()->route('metas')->with('error',
                'Esta meta no acepta aportes en su estado actual.');
        }

        $datos = $request->validate([
            'monto' => ['required', 'numeric', 'gt:0', 'decimal:0,2', 'max:99999999.99'],
            'fecha' => ['required', 'date'],
            'nota' => ['nullable', 'string', 'max:255'],
        ], [
            'monto.gt' => 'El aporte debe ser mayor que cero.',
        ]);

        DB::transaction(function () use ($metaAhorro, $datos) {
            $metaAhorro->aportes()->create([
                'monto' => $datos['monto'],
                'fecha' => $datos['fecha'],
                'nota' => $datos['nota'] ?? null,
            ]);

            $acumulado = (float) $metaAhorro->aportes()->sum('monto');

            if ($acumulado >= (float) $metaAhorro->monto_objetivo) {
                $metaAhorro->estado = 'completada';
                $metaAhorro->save();
            }
        });

        return redirect()->route('metas')->with('success', 'Aporte registrado correctamente.');
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'tipo_meta' => ['required', 'in:meta_ahorro,fondo_emergencia'],
            'monto_objetivo' => ['required', 'numeric', 'gt:0', 'decimal:0,2', 'max:99999999.99'],
            'fecha_objetivo' => ['required', 'date'],
        ], [
            'monto_objetivo.gt' => 'El monto objetivo debe ser mayor que cero.',
        ]);
    }
}
