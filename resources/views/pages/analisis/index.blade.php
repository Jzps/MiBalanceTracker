@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/pages/analisis.css') }}">
@endpush

@section('content')
<section class="modulo-cabecera">
    <div>
        <h1>Análisis financiero</h1>
        <p>Distribución de tus gastos, evolución mensual y comparación de periodos.</p>
    </div>
</section>

<!-- RF-19: información estadística -->
<div class="rejilla rejilla-kpis">
    <article class="tarjeta kpi">
        <h3>Ingresos (3 meses)</h3>
        <p class="kpi-valor">$ {{ number_format($ingresos, 0, ',', '.') }}</p>
        <p class="kpi-pie">Ingresos registrados durante los últimos tres meses</p>
    </article>

    <article class="tarjeta kpi">
        <h3>Gastos (3 meses)</h3>
        <p class="kpi-valor">$ {{ number_format($gastos, 0, ',', '.') }}</p>
        <p class="kpi-pie">Gastos registrados durante los últimos tres meses</p>
    </article>

    <article class="tarjeta kpi">
        <h3>Ahorro acumulado</h3>
        <p class="kpi-valor">$ {{ number_format($ahorro, 0, ',', '.') }}</p>
        <p class="kpi-pie">Ingresos menos gastos del periodo</p>
    </article>
</div>

<div class="rejilla rejilla-2">
    <!-- RF-21: contenedor del gráfico de gastos por categoría -->
    <section class="tarjeta">
        <h2>Gastos por categoría</h2>

        @if($gastosPorCategoria->isNotEmpty())
            <ul class="grafico-barras">
                @foreach($gastosPorCategoria as $item)
                    <li>
                        <span>{{ $item['categoria'] }}</span>
                        <strong>$ {{ number_format($item['total'], 0, ',', '.') }}</strong>
                        <div class="barra-progreso">
                            <div class="barra-progreso-valor" style="width: {{ $item['porcentaje'] }}%"></div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="estado-vacio">
                <h3>Sin datos</h3>
                <p>Todavía no hay gastos registrados.</p>
            </div>
        @endif
    </section>

    <!-- RF-21: contenedor del gráfico de evolución -->
    <section class="tarjeta">
        <h2>Evolución de gastos</h2>

        @if($evolucionGastos->isNotEmpty())
            <ul class="grafico-barras">
                @foreach($evolucionGastos as $item)
                    <li>
                        <span>{{ $item['periodo'] }}</span>
                        <strong>$ {{ number_format($item['total'], 0, ',', '.') }}</strong>
                        <div class="barra-progreso">
                            <div class="barra-progreso-valor" style="width: {{ $item['porcentaje'] }}%"></div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="estado-vacio">
                <h3>Sin datos</h3>
                <p>Todavía no hay gastos registrados.</p>
            </div>
        @endif
    </section>
</div>

<!-- RF-20: comparación entre períodos -->
<section class="tarjeta">
    <h2>Comparación mensual</h2>

    @if($comparacionMensual->isNotEmpty())
        <div class="tabla-contenedor">
            <table class="tabla">
                <thead>
                    <tr>
                        <th scope="col">Periodo</th>
                        <th scope="col" class="num">Ingresos</th>
                        <th scope="col" class="num">Gastos</th>
                        <th scope="col" class="num">Ahorro</th>
                        <th scope="col" class="num">Disponible</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($comparacionMensual as $item)
                        <tr>
                            <td>{{ $item['periodo'] }}</td>
                            <td class="num">$ {{ number_format($item['ingresos'], 0, ',', '.') }}</td>
                            <td class="num">$ {{ number_format($item['gastos'], 0, ',', '.') }}</td>
                            <td class="num">$ {{ number_format($item['ahorro'], 0, ',', '.') }}</td>
                            <td class="num">$ {{ number_format($item['disponible'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="estado-vacio">
            <h3>Sin datos</h3>
            <p>Todavía no hay movimientos registrados.</p>
        </div>
    @endif
</section>
@endsection