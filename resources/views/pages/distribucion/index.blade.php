@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/pages/distribucion.css') }}">
@endpush

@section('content')
<section class="modulo-cabecera">
    <div>
        <h1>Distribución 50/30/20</h1>
        <p>Referencia para dividir tu ingreso entre necesidades, deseos y ahorro.</p>
    </div>
</section>

@if(session('success'))
    <div class="alerta alerta-exito" role="alert">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="alerta alerta-error" role="alert">
        @foreach($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif

<div class="rejilla rejilla-2">
    <!-- RN-14: la distribución se presenta como referencia orientativa -->
    <section class="tarjeta">
        <h2>Tu referencia actual</h2>
        <p class="campo-ayuda">Porcentajes de referencia según tu configuración actual.</p>

        <div class="rejilla rejilla-3">
            <div class="mini-dato">
                <span class="mini-dato-etiqueta">Necesidades</span>
                <strong>{{ $porcentajeNecesidades }} %</strong>
                <p class="kpi-pie">$ {{ number_format($referenciaNecesidades, 0, ',', '.') }}</p>
            </div>

            <div class="mini-dato">
                <span class="mini-dato-etiqueta">Deseos</span>
                <strong>{{ $porcentajeDeseos }} %</strong>
                <p class="kpi-pie">$ {{ number_format($referenciaDeseos, 0, ',', '.') }}</p>
            </div>

            <div class="mini-dato">
                <span class="mini-dato-etiqueta">Ahorro</span>
                <strong>{{ $porcentajeAhorro }} %</strong>
                <p class="kpi-pie">$ {{ number_format($referenciaAhorro, 0, ',', '.') }}</p>
            </div>
        </div>

        <p class="pie-tabla">
            Ingreso fijo mensual: $ {{ number_format($ingresoFijo, 0, ',', '.') }}.
            El ingreso fijo se configura en
            <a href="{{ route('configuracion') }}">Configuración</a>.
        </p>
    </section>

    <!-- RF-24: personalización de la distribución -->
    <section class="tarjeta">
        <h2>Personalizar porcentajes</h2>

        <form class="formulario" action="{{ route('distribucion.actualizar') }}" method="POST">
            @csrf

            <div class="rejilla rejilla-3 rejilla-ajustada">
                <div class="campo">
                    <label for="porcentaje_necesidades">Necesidades %</label>
                    <input type="number" id="porcentaje_necesidades" name="porcentaje_necesidades"
                           min="0" max="100" step="0.01" required
                           value="{{ old('porcentaje_necesidades', $porcentajeNecesidades) }}">
                </div>

                <div class="campo">
                    <label for="porcentaje_deseos">Deseos %</label>
                    <input type="number" id="porcentaje_deseos" name="porcentaje_deseos"
                           min="0" max="100" step="0.01" required
                           value="{{ old('porcentaje_deseos', $porcentajeDeseos) }}">
                </div>

                <div class="campo">
                    <label for="porcentaje_ahorro">Ahorro %</label>
                    <input type="number" id="porcentaje_ahorro" name="porcentaje_ahorro"
                           min="0" max="100" step="0.01" required
                           value="{{ old('porcentaje_ahorro', $porcentajeAhorro) }}">
                </div>
            </div>

            <p class="campo-ayuda">La suma de los tres porcentajes debe ser exactamente 100 %.</p>

            <div class="acciones-formulario">
                <button type="submit" class="btn btn-primario">Guardar distribución</button>
            </div>
        </form>
    </section>
</div>

<!-- RF-23: comparación entre la referencia y los gastos reales -->
<section class="tarjeta">
    <h2>Referencia vs. gasto real</h2>

    @if($comparacion->isNotEmpty())
        <ul class="lista-progreso">
            @foreach($comparacion as $item)
                <li class="mini-dato">
                    <span class="mini-dato-etiqueta">
                        {{ $item['nombre'] }} ({{ $item['porcentaje'] }} %)
                    </span>

                    <strong>
                        $ {{ number_format($item['real'], 0, ',', '.') }}
                        / $ {{ number_format($item['referencia'], 0, ',', '.') }}
                    </strong>

                    <p class="kpi-pie">
                        Diferencia: $ {{ number_format($item['diferencia'], 0, ',', '.') }}
                    </p>

                    <div class="barra-progreso">
                        <div class="barra-progreso-valor" style="width: {{ $item['anchoBarra'] }}%"></div>
                    </div>

                    <p class="kpi-pie">
                        {{ $item['porcentajeUtilizado'] }} % de la referencia utilizada
                    </p>
                </li>
            @endforeach
        </ul>
    @else
        <div class="estado-vacio">
            <h3>No hay datos registrados</h3>
            <p>Aún no hay gastos registrados.</p>
        </div>
    @endif
</section>
@endsection