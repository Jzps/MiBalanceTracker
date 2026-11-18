@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/pages/deudas.css') }}">
@endpush

@section('content')
<section class="modulo-cabecera">
    <div>
        <h1>Deudas</h1>
        <p>Registra tus obligaciones financieras, consulta el saldo pendiente y controla los pagos.</p>
    </div>
</section>

@if (session('success'))
    <div class="alerta alerta-info" role="status">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="alerta alerta-error" role="alert">{{ session('error') }}</div>
@endif
@if ($errors->any())
    <div class="alerta alerta-error" role="alert">
        @foreach ($errors->all() as $error)
            {{ $error }}@if (!$loop->last)<br>@endif
        @endforeach
    </div>
@endif

<div class="rejilla rejilla-formulario">
    <!-- RF-17: registro de deudas -->
    <section class="tarjeta">
        <h2>{{ $deudaEditar ? 'Editar deuda' : 'Nueva deuda' }}</h2>
        <form class="formulario" action="{{ $deudaEditar ? route('deudas.update', $deudaEditar->id_deuda) : route('deudas.store') }}" method="post">
            @csrf
            @if ($deudaEditar)
                @method('PUT')
            @endif

            <div class="campo">
                <label for="acreedor">Acreedor *</label>
                <input type="text" id="acreedor" name="acreedor" required maxlength="150"
                       value="{{ old('acreedor', $deudaEditar->acreedor ?? '') }}" placeholder=" ">
            </div>

            <div class="rejilla rejilla-2 rejilla-ajustada">
                <div class="campo">
                    <label for="monto_inicial">Monto inicial *</label>
                    <div class="campo-moneda">
                        <span aria-hidden="true">$</span>
                        <input type="number" id="monto_inicial" name="monto_inicial" min="0.01" step="0.01" max="99999999.99" required
                               value="{{ old('monto_inicial', $deudaEditar->monto_inicial ?? '') }}"
                               inputmode="decimal" placeholder=" ">
                    </div>
                    @error('monto_inicial')<span class="campo-ayuda">{{ $message }}</span>@enderror
                </div>
                <div class="campo">
                    <label for="fecha_vencimiento">Fecha de vencimiento *</label>
                    <input type="date" id="fecha_vencimiento" name="fecha_vencimiento" required
                           value="{{ old('fecha_vencimiento', $deudaEditar?->fecha_vencimiento?->toDateString() ?? '') }}">
                </div>
            </div>

            <div class="campo">
                <label for="pago_minimo">Pago mínimo mensual</label>
                <div class="campo-moneda">
                    <span aria-hidden="true">$</span>
                    <input type="number" id="pago_minimo" name="pago_minimo" min="0" step="0.01" max="99999999.99"
                           value="{{ old('pago_minimo', $deudaEditar->pago_minimo ?? '0') }}"
                           inputmode="decimal" placeholder=" ">
                </div>
            </div>

            @if ($deudaEditar)
                <div class="campo">
                    <label for="estado_deuda">Estado</label>
                    <select id="estado_deuda" name="estado">
                        <option value="pendiente" @selected(old('estado', $deudaEditar->estado) === 'pendiente')>Pendiente</option>
                        <option value="cancelada" @selected(old('estado', $deudaEditar->estado) === 'cancelada')>Cancelada</option>
                    </select>
                    <p class="campo-ayuda">El estado "pagada" se determina automáticamente con los pagos.</p>
                </div>
            @endif

            <div class="acciones-formulario">
                @if ($deudaEditar)
                    <a class="btn btn-secundario" href="{{ route('deudas') }}">Cancelar edición</a>
                @endif
                <button type="submit" class="btn btn-primario">{{ $deudaEditar ? 'Actualizar deuda' : 'Registrar deuda' }}</button>
            </div>
        </form>
    </section>

    <!-- RF-18: registro de pagos -->
    <section class="tarjeta">
        <h2>Registrar pago</h2>
        @php $primeraDeuda = $deudas->first()['deuda'] ?? null; @endphp
        <form id="frm-pago" class="formulario" method="post"
              action="{{ $primeraDeuda ? route('pagos.store', $primeraDeuda->id_deuda) : route('deudas') }}"
              data-base="{{ route('pagos.store', ['deuda' => '__DEUDA__']) }}">
            @csrf

            <div class="campo">
                <label for="id_deuda">Deuda *</label>
                <select id="id_deuda" name="id_deuda" required>
                    <option value="">Selecciona...</option>
                    @foreach ($deudas as $item)
                        @if ($item['deuda']->estado === 'pendiente' && $item['saldo'] > 0)
                            <option value="{{ $item['deuda']->id_deuda }}">{{ $item['deuda']->acreedor }}</option>
                        @endif
                    @endforeach
                </select>
                @if ($deudas->isEmpty())
                    <p class="campo-ayuda">Registra una deuda antes de registrar pagos.</p>
                @endif
            </div>

            <div class="campo">
                <label for="id_categoria">Categoría de gasto *</label>
                <select id="id_categoria" name="id_categoria" required>
                    <option value="">Selecciona...</option>
                    @foreach ($categoriasGasto as $categoria)
                        <option value="{{ $categoria->id_categoria }}"
                                @selected((string) old('id_categoria') === (string) $categoria->id_categoria)>
                            {{ $categoria->nombre }}
                        </option>
                    @endforeach
                </select>
                @error('id_categoria')<span class="campo-ayuda">{{ $message }}</span>@enderror
                @if ($categoriasGasto->isEmpty())
                    <p class="campo-ayuda">Crea una categoría de gasto activa antes de registrar pagos.</p>
                @endif
            </div>

            <div class="rejilla rejilla-2 rejilla-ajustada">
                <div class="campo">
                    <label for="monto_pago">Monto pagado *</label>
                    <div class="campo-moneda">
                        <span aria-hidden="true">$</span>
                        <input type="number" id="monto_pago" name="monto" min="0.01" step="0.01" max="99999999.99" required
                               value="{{ old('monto') }}" inputmode="decimal" placeholder=" ">
                    </div>
                    @error('monto')<span class="campo-ayuda">{{ $message }}</span>@enderror
                </div>
                <div class="campo">
                    <label for="fecha_pago">Fecha *</label>
                    <input type="date" id="fecha_pago" name="fecha" required value="{{ old('fecha') }}">
                </div>
            </div>

            <div class="campo">
                <label for="descripcion_pago">Descripción</label>
                <input type="text" id="descripcion_pago" name="descripcion" maxlength="150"
                       value="{{ old('descripcion') }}" placeholder=" ">
            </div>

            <div class="campo">
                <label for="metodo_pago">Método de pago</label>
                <select id="metodo_pago" name="metodo_pago">
                    <option value="">No aplica</option>
                    <option value="efectivo" @selected(old('metodo_pago') === 'efectivo')>Efectivo</option>
                    <option value="tarjeta_debito" @selected(old('metodo_pago') === 'tarjeta_debito')>Tarjeta de débito</option>
                    <option value="tarjeta_credito" @selected(old('metodo_pago') === 'tarjeta_credito')>Tarjeta de crédito</option>
                    <option value="transferencia" @selected(old('metodo_pago') === 'transferencia')>Transferencia</option>
                </select>
            </div>

            <div class="acciones-formulario">
                <button type="submit" class="btn btn-primario">Registrar pago</button>
            </div>
        </form>
    </section>
</div>

<!-- Listado de deudas y su historial de pagos -->
<section class="tarjeta">
    <h2>Mis deudas</h2>
    <div class="tabla-contenedor">
        <table class="tabla">
            <thead>
                <tr>
                    <th scope="col">Acreedor</th>
                    <th scope="col" class="num">Monto inicial</th>
                    <th scope="col" class="num">Pagado</th>
                    <th scope="col" class="num">Saldo pendiente</th>
                    <th scope="col" class="num">Progreso</th>
                    <th scope="col" class="num">Pago mínimo</th>
                    <th scope="col">Vencimiento</th>
                    <th scope="col">Estado</th>
                    <th scope="col">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($deudas as $item)
                    @php $deuda = $item['deuda']; @endphp
                    <tr>
                        <td>{{ $deuda->acreedor }}</td>
                        <td class="num">${{ number_format((float) $deuda->monto_inicial, 2) }}</td>
                        <td class="num">${{ number_format($item['pagado'], 2) }}</td>
                        <td class="num">${{ number_format($item['saldo'], 2) }}</td>
                        <td class="num">{{ $item['porcentaje'] }}%</td>
                        <td class="num">${{ number_format((float) $deuda->pago_minimo, 2) }}</td>
                        <td>{{ $deuda->fecha_vencimiento->toDateString() }}</td>
                        <td>
                            <span class="etiqueta {{ $deuda->estado === 'pagada' ? 'etiqueta-exito' : ($deuda->estado === 'cancelada' ? 'etiqueta-alerta' : 'etiqueta-info') }}">
                                {{ ucfirst($deuda->estado) }}
                            </span>
                        </td>
                        <td>
                            <a class="btn btn-fantasma btn-pequeno" href="{{ route('deudas', ['editar' => $deuda->id_deuda]) }}">Editar</a>
                            @if ($deuda->estado !== 'cancelada')
                                <form method="POST" action="{{ route('deudas.update', $deuda->id_deuda) }}" style="display: inline;">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="acreedor" value="{{ $deuda->acreedor }}">
                                    <input type="hidden" name="monto_inicial" value="{{ $deuda->monto_inicial }}">
                                    <input type="hidden" name="fecha_vencimiento" value="{{ $deuda->fecha_vencimiento->toDateString() }}">
                                    <input type="hidden" name="pago_minimo" value="{{ $deuda->pago_minimo }}">
                                    <input type="hidden" name="estado" value="cancelada">
                                    <button type="submit" class="btn btn-fantasma btn-pequeno">Cancelar deuda</button>
                                </form>
                            @endif
                            @if ($item['pagos']->isEmpty())
                                <form method="POST" action="{{ route('deudas.destroy', $deuda->id_deuda) }}" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-fantasma btn-pequeno">Eliminar</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if ($deudas->isEmpty())
        <div class="estado-vacio">
            <h3>Sin deudas</h3>
            <p>Aún no tienes deudas registradas. Registra la primera con el formulario.</p>
        </div>
    @endif
</section>

@if ($deudas->isNotEmpty())
    <section class="tarjeta">
        <h2>Historial de pagos</h2>
        <ul class="lista-progreso">
            @foreach ($deudas as $item)
                @foreach ($item['pagos'] as $pago)
                    <li>
                        <div class="progreso-info">
                            <strong>{{ $item['deuda']->acreedor }}</strong>
                            <span>${{ number_format((float) $pago->monto, 2) }} · {{ $pago->fecha->toDateString() }}</span>
                        </div>
                        <p class="progreso-pie">{{ $pago->descripcion }} · Movimiento #{{ $pago->id_movimiento }}</p>
                    </li>
                @endforeach
            @endforeach
        </ul>
    </section>
@endif
@endsection
