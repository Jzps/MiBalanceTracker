<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Deuda;
use App\Models\Movimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeudaController extends Controller
{
    public function index(Request $request)
    {
        $idUsuario = Auth::id();

        $deudas = Deuda::query()
            ->where('id_usuario', $idUsuario)
            ->with('pagos')
            ->orderByDesc('fecha_vencimiento')
            ->get()
            ->map(function (Deuda $deuda) {
                $pagado = (float) $deuda->pagos->sum('monto');
                $inicial = (float) $deuda->monto_inicial;

                return [
                    'deuda' => $deuda,
                    'pagado' => $pagado,
                    'saldo' => max($inicial - $pagado, 0),
                    'porcentaje' => round(($pagado / $inicial) * 100, 2),
                    'pagos' => $deuda->pagos->sortByDesc('fecha'),
                ];
            });

        $deudaEditar = $request->filled('editar')
            ? Deuda::query()->where('id_usuario', $idUsuario)->find($request->editar)
            : null;

        $categoriasGasto = Categoria::query()
            ->where('id_usuario', $idUsuario)
            ->where('activa', true)
            ->where('tipo_movimiento', 'gasto')
            ->orderBy('nombre')
            ->get();

        return view('pages.deudas.index', compact('deudas', 'deudaEditar', 'categoriasGasto') + [
            'tituloPagina' => 'Deudas',
            'paginaActiva' => 'deudas',
        ]);
    }

    public function store(Request $request)
    {
        $datos = $this->validarDeuda($request);

        Deuda::create([
            'id_usuario' => Auth::id(),
            'acreedor' => $datos['acreedor'],
            'monto_inicial' => $datos['monto_inicial'],
            'fecha_vencimiento' => $datos['fecha_vencimiento'],
            'pago_minimo' => $datos['pago_minimo'] ?? 0,
            'estado' => 'pendiente',
        ]);

        return redirect()->route('deudas')->with('success', 'Deuda registrada correctamente.');
    }

    public function update(Request $request, int $id)
    {
        $deuda = Deuda::query()
            ->where('id_usuario', Auth::id())
            ->findOrFail($id);

        $datos = $this->validarDeuda($request);

        $request->validate([
            'estado' => ['nullable', 'in:pendiente,pagada,cancelada'],
        ]);

        $pagado = (float) $deuda->pagos()->sum('monto');

        if ((float) $datos['monto_inicial'] < $pagado) {
            throw ValidationException::withMessages([
                'monto_inicial' => 'El monto inicial no puede ser menor que el total ya pagado.',
            ]);
        }

        $saldo = (float) $datos['monto_inicial'] - $pagado;
        $estado = $request->input('estado', $deuda->estado);

        if ($estado === 'pagada' && $saldo > 0) {
            throw ValidationException::withMessages([
                'estado' => 'No se puede marcar como pagada mientras exista saldo pendiente.',
            ]);
        }

        if ($estado === 'pendiente' && $saldo <= 0) {
            $estado = 'pagada';
        }

        $deuda->update([
            'acreedor' => $datos['acreedor'],
            'monto_inicial' => $datos['monto_inicial'],
            'fecha_vencimiento' => $datos['fecha_vencimiento'],
            'pago_minimo' => $datos['pago_minimo'] ?? 0,
            'estado' => $estado,
        ]);

        return redirect()->route('deudas')->with('success', 'Deuda actualizada correctamente.');
    }

    public function destroy(int $id)
    {
        $deuda = Deuda::query()
            ->where('id_usuario', Auth::id())
            ->findOrFail($id);

        if ($deuda->pagos()->exists()) {
            return redirect()->route('deudas')->with('error',
                'No se puede eliminar la deuda porque tiene pagos registrados. Cancélala para conservar el historial.');
        }

        $deuda->delete();

        return redirect()->route('deudas')->with('success', 'Deuda eliminada correctamente.');
    }

    public function storePago(Request $request, int $deuda)
    {
        $deudaModel = Deuda::query()
            ->where('id_usuario', Auth::id())
            ->findOrFail($deuda);

        $datos = $request->validate([
            'monto' => ['required', 'numeric', 'gt:0', 'decimal:0,2', 'max:99999999.99'],
            'fecha' => ['required', 'date'],
            'id_categoria' => ['required', 'integer'],
            'descripcion' => ['nullable', 'string', 'max:150'],
            'metodo_pago' => ['nullable', 'string', 'max:50'],
        ], [
            'monto.gt' => 'El pago debe ser mayor que cero.',
        ]);

        if ($deudaModel->estado !== 'pendiente') {
            return redirect()->route('deudas')->with('error',
                'La deuda no admite pagos en su estado actual.');
        }

        $pagado = (float) $deudaModel->pagos()->sum('monto');
        $saldo = (float) $deudaModel->monto_inicial - $pagado;

        if ($saldo <= 0) {
            return redirect()->route('deudas')->with('error', 'La deuda ya no tiene saldo pendiente.');
        }

        if ((float) $datos['monto'] > $saldo) {
            throw ValidationException::withMessages([
                'monto' => 'El pago no puede superar el saldo pendiente de la deuda.',
            ]);
        }

        $categoriaValida = Categoria::query()
            ->where('id_usuario', Auth::id())
            ->where('id_categoria', $datos['id_categoria'])
            ->where('activa', true)
            ->where('tipo_movimiento', 'gasto')
            ->exists();

        if (! $categoriaValida) {
            throw ValidationException::withMessages([
                'id_categoria' => 'La categoría seleccionada no es válida para un pago.',
            ]);
        }

        DB::transaction(function () use ($deudaModel, $datos) {
            $descripcion = trim((string) ($datos['descripcion'] ?? ''));
            if ($descripcion === '') {
                $descripcion = 'Pago de deuda: '.$deudaModel->acreedor;
            }

            $movimiento = Movimiento::create([
                'id_usuario' => Auth::id(),
                'id_categoria' => $datos['id_categoria'],
                'monto' => $datos['monto'],
                'fecha' => $datos['fecha'],
                'tipo' => 'gasto',
                'descripcion' => mb_substr($descripcion, 0, 150),
                'metodo_pago' => $datos['metodo_pago'] ?? null,
                'notas' => null,
            ]);

            $deudaModel->pagos()->create([
                'id_movimiento' => $movimiento->id_movimiento,
                'monto' => $datos['monto'],
                'fecha' => $datos['fecha'],
                'descripcion' => mb_substr($descripcion, 0, 150),
            ]);

            $nuevoSaldo = (float) $deudaModel->monto_inicial - (float) $deudaModel->pagos()->sum('monto');

            if ($nuevoSaldo <= 0) {
                $deudaModel->estado = 'pagada';
                $deudaModel->save();
            }
        });

        return redirect()->route('deudas')->with('success', 'Pago registrado correctamente.');
    }

    private function validarDeuda(Request $request): array
    {
        return $request->validate([
            'acreedor' => ['required', 'string', 'max:150'],
            'monto_inicial' => ['required', 'numeric', 'gt:0', 'decimal:0,2', 'max:99999999.99'],
            'fecha_vencimiento' => ['required', 'date'],
            'pago_minimo' => ['nullable', 'numeric', 'gte:0', 'decimal:0,2', 'max:99999999.99'],
        ], [
            'monto_inicial.gt' => 'El monto inicial debe ser mayor que cero.',
            'pago_minimo.gte' => 'El pago mínimo no puede ser negativo.',
        ]);
    }
}
