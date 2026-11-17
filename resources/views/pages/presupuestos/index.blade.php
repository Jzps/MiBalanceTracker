@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/pages/presupuestos.css') }}">
@endpush

@section('content')
<section class="modulo-cabecera">
    <div>
        <h1>Presupuestos</h1>
        <p>Define límites de gasto por categoría y período, y controla tu consumo.</p>
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
    <!-- RF-10: creación de presupuestos -->
    <section class="tarjeta">
        <h2>{{ $presupuestoEditar ? 'Editar presupuesto' : 'Nuevo presupuesto' }}</h2>
        <form class="formulario" action="{{ $presupuestoEditar ? route('presupuestos.update', $presupuestoEditar->id_presupuesto) : route('presupuestos.store') }}" method="post">
            @csrf
            @if ($presupuestoEditar)
                @method('PUT')
            @endif

            <div class="campo">
                <label for="id_categoria">Categoría *</label>
                <select id="id_categoria" name="id_categoria" required>
                    <option value="">Selecciona...</option>
                    @foreach ($categorias as $categoria)
                        <option value="{{ $categoria->id_categoria }}"
                                @selected((string) old('id_categoria', $presupuestoEditar->id_categoria ?? '') === (string) $categoria->id_categoria)>
                            {{ $categoria->nombre }}
                        </option>
                    @endforeach
                </select>
                @error('id_categoria')<span class="campo-ayuda">{{ $message }}</span>@enderror
                @if ($categorias->isEmpty())
                    <p class="campo-ayuda">Crea una categoría de gasto activa antes de definir un presupuesto.</p>
                @endif
            </div>

            <div class="rejilla rejilla-2 rejilla-ajustada">
                <div class="campo">
                    <label for="fecha_inicio">Fecha de inicio *</label>
                    <input type="date" id="fecha_inicio" name="fecha_inicio" required
                           value="{{ old('fecha_inicio', $presupuestoEditar?->fecha_inicio?->toDateString() ?? '') }}">
                    @error('fecha_inicio')<span class="campo-ayuda">{{ $message }}</span>@enderror
                </div>
                <div class="campo">
                    <label for="fecha_fin">Fecha de fin *</label>
                    <input type="date" id="fecha_fin" name="fecha_fin" required
                           value="{{ old('fecha_fin', $presupuestoEditar?->fecha_fin?->toDateString() ?? '') }}">
                    @error('fecha_fin')<span class="campo-ayuda">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="rejilla rejilla-2 rejilla-ajustada">
                <div class="campo">
                    <label for="monto_limite">Límite del presupuesto *</label>
                    <div class="campo-moneda">
                        <span aria-hidden="true">$</span>
                        <input type="number" id="monto_limite" name="monto_limite" min="1" step="0.01" max="99999999.99" required
                               value="{{ old('monto_limite', $presupuestoEditar->monto_limite ?? '') }}"
                               inputmode="decimal" placeholder=" ">
                    </div>
                    @error('monto_limite')<span class="campo-ayuda">{{ $message }}</span>@enderror
                </div>
                <div class="campo">
                    <label for="porcentaje_alerta">Alerta al %</label>
                    <input type="number" id="porcentaje_alerta" name="porcentaje_alerta" min="1" max="100" step="1"
                           value="{{ old('porcentaje_alerta', $presupuestoEditar->porcentaje_alerta ?? '80') }}" required>
                    @error('porcentaje_alerta')<span class="campo-ayuda">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="acciones-formulario">
                <button type="reset" class="btn btn-fantasma">Limpiar</button>
                @if ($presupuestoEditar)
                    <a class="btn btn-secundario" href="{{ route('presupuestos') }}">Cancelar edición</a>
                @endif
                <button type="submit" class="btn btn-primario">{{ $presupuestoEditar ? 'Actualizar presupuesto' : 'Crear presupuesto' }}</button>
            </div>
        </form>
    </section>

    <!-- RF-11 / RF-12: progreso y alertas calculados desde movimientos reales -->
    <section class="tarjeta">
        <h2>Mis presupuestos</h2>
        <ul class="lista-progreso">
            @foreach ($presupuestos as $item)
                @php $presupuesto = $item['presupuesto']; @endphp
                <li>
                    <div class="progreso-info">
                        <strong>{{ $item['categoria']->nombre }}</strong>
                        <span>
                            {{ $presupuesto->fecha_inicio->toDateString() }} — {{ $presupuesto->fecha_fin->toDateString() }}
                        </span>
                    </div>
                    <div class="barra" role="img" aria-label="Uso del presupuesto: {{ $item['porcentaje'] }}%">
                        <span class="barra-relleno
                            @if ($item['estado'] === 'excedido') barra-excedido
                            @elseif ($item['estado'] === 'preventivo') barra-alerta @endif"
                              style="--valor: {{ $item['porcentaje'] }}%"></span>
                    </div>
                    <p class="progreso-pie">
                        Gastado ${{ number_format($item['gastado'], 2) }} de ${{ number_format((float) $presupuesto->monto_limite, 2) }} ·
                        Disponible ${{ number_format($item['disponible'], 2) }} ·
                        {{ $item['porcentaje'] }}% utilizado
                    </p>
                    @if ($item['mensaje'] !== '')
                        <p>
                            <span class="etiqueta {{ $item['estado'] === 'excedido' ? 'etiqueta-error' : 'etiqueta-alerta' }}">
                                {{ $item['mensaje'] }}
                            </span>
                        </p>
                    @endif
                    <div class="acciones-formulario">
                        <a class="btn btn-fantasma btn-pequeno" href="{{ route('presupuestos', ['editar' => $presupuesto->id_presupuesto]) }}">Editar</a>
                        <form method="POST" action="{{ route('presupuestos.destroy', $presupuesto->id_presupuesto) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-fantasma btn-pequeno">Eliminar</button>
                        </form>
                    </div>
                </li>
            @endforeach
        </ul>
        @if ($presupuestos->isEmpty())
            <div class="estado-vacio">
                <h3>Sin presupuestos</h3>
                <p>Aún no tienes presupuestos. Crea uno con el formulario.</p>
            </div>
        @endif
    </section>
</div>
@endsection
