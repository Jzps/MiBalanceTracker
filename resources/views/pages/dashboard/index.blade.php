@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/pages/dashboard.css') }}">
@endpush

@section('content')
<section class="modulo-cabecera">
    <div>
        <h1>Dashboard</h1>
        <p>Resumen de tu situación financiera.</p>
    </div>
</section>

<!-- RF-04: resumen de la situación financiera -->
<section aria-label="Indicadores del periodo actual">
    <div class="rejilla rejilla-kpis">
        <article class="tarjeta kpi">
            <h3>Ingresos del mes</h3>
            <p class="kpi-valor">$ {{ number_format($ingresos, 0, ',', '.') }}</p>
            <p class="kpi-pie">Ingreso fijo e ingresos adicionales</p>
        </article>

        <article class="tarjeta kpi">
            <h3>Gastos del mes</h3>
            <p class="kpi-valor">$ {{ number_format($gastos, 0, ',', '.') }}</p>
            <p class="kpi-pie">Gastos registrados durante el mes</p>
        </article>

        <article class="tarjeta kpi">
            <h3>Disponible</h3>
            <p class="kpi-valor">$ {{ number_format($disponible, 0, ',', '.') }}</p>
            <p class="kpi-pie">Ingresos menos gastos</p>
        </article>

        <article class="tarjeta kpi">
            <h3>Ahorro del mes</h3>
            <p class="kpi-valor">$ {{ number_format($ahorro, 0, ',', '.') }}</p>
            <p class="kpi-pie">Aportes a metas y fondo</p>
        </article>
    </div>
    <p class="pie-tabla">Los indicadores se actualizan automáticamente con los registros financieros.</p>
</section>

<div class="rejilla rejilla-2">
    <!-- RF-11: progreso de los presupuestos -->
    <section class="tarjeta">
        <h2>Presupuestos</h2>

        @forelse($presupuestos as $item)
            <div class="mini-dato">
                <span class="mini-dato-etiqueta">{{ $item['categoria']->nombre ?? 'Categoría' }}</span>

                <strong>
                    $ {{ number_format($item['gastado'], 0, ',', '.') }}
                    / $ {{ number_format($item['presupuesto']->monto_limite, 0, ',', '.') }}
                </strong>

                <p class="kpi-pie">Disponible: $ {{ number_format($item['disponible'], 0, ',', '.') }}</p>

                <div class="barra-progreso">
                    <div class="barra-progreso-valor" style="width: {{ $item['anchoBarra'] }}%"></div>
                </div>

                <p class="kpi-pie">{{ $item['porcentaje'] }}% utilizado</p>
            </div>
        @empty
            <div class="estado-vacio">
                <h3>No hay datos registrados</h3>
                <p>Aún no tienes presupuestos. Crea uno desde el módulo Presupuestos.</p>
            </div>
        @endforelse
    </section>

    <!-- RF-16: progreso de las metas -->
    <section class="tarjeta">
        <h2>Metas de ahorro</h2>

        @forelse($metas as $item)
            <div class="mini-dato">
                <span class="mini-dato-etiqueta">{{ $item['meta']->nombre }}</span>

                <strong>
                    $ {{ number_format($item['acumulado'], 0, ',', '.') }}
                    / $ {{ number_format($item['meta']->monto_objetivo, 0, ',', '.') }}
                </strong>

                <p class="kpi-pie">Pendiente: $ {{ number_format($item['pendiente'], 0, ',', '.') }}</p>

                <div class="barra-progreso">
                    <div class="barra-progreso-valor" style="width: {{ $item['anchoBarra'] }}%"></div>
                </div>

                <p class="kpi-pie">{{ $item['progreso'] }}% completado</p>
            </div>
        @empty
            <div class="estado-vacio">
                <h3>No hay datos registrados</h3>
                <p>Aún no tienes metas de ahorro. Crea la primera desde el módulo Metas.</p>
            </div>
        @endforelse
    </section>
</div>

<!-- RF-06: últimos movimientos -->
<section class="tarjeta">
    <h2>Movimientos recientes</h2>

    @if($movimientos->isNotEmpty())
        <div class="tabla-contenedor">
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Descripción</th>
                        <th>Categoría</th>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th class="num">Monto</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($movimientos as $movimiento)
                        <tr>
                            <td>{{ $movimiento->descripcion }}</td>
                            <td>{{ $movimiento->categoria->nombre ?? 'Sin categoría' }}</td>
                            <td>{{ $movimiento->fecha->format('d/m/Y') }}</td>
                            <td>{{ ucfirst($movimiento->tipo) }}</td>
                            <td class="num">$ {{ number_format($movimiento->monto, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="estado-vacio">
            <h3>No hay datos registrados</h3>
            <p>Aún no hay movimientos registrados. Ve a Movimientos para registrar el primero.</p>
        </div>
    @endif
</section>

<!-- RN-03: el ingreso fijo sirve como referencia de la distribución -->
<section class="tarjeta">
    <h2>Distribución 50/30/20 del mes</h2>
    <div class="rejilla rejilla-3">
        <div class="mini-dato">
            <span class="mini-dato-etiqueta">Necesidades ({{ $porcentajeNecesidades }} %)</span>
            <strong class="kpi-valor">$ {{ number_format($referenciaNecesidades, 0, ',', '.') }}</strong>
        </div>
        <div class="mini-dato">
            <span class="mini-dato-etiqueta">Deseos ({{ $porcentajeDeseos }} %)</span>
            <strong class="kpi-valor">$ {{ number_format($referenciaDeseos, 0, ',', '.') }}</strong>
        </div>
        <div class="mini-dato">
            <span class="mini-dato-etiqueta">Ahorro ({{ $porcentajeAhorro }} %)</span>
            <strong class="kpi-valor">$ {{ number_format($referenciaAhorro, 0, ',', '.') }}</strong>
        </div>
    </div>
    <p class="pie-tabla">Referencia según tu ingreso fijo mensual configurado.</p>
</section>
@endsection