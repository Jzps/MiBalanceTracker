<li>
    <div class="progreso-info">
        <strong>{{ $meta->nombre }}</strong>
        <span class="etiqueta {{ $meta->estado === 'completada' ? 'etiqueta-exito' : ($meta->estado === 'cancelada' || $meta->estado === 'pausada' ? 'etiqueta-alerta' : 'etiqueta-info') }}">
            {{ ucfirst($meta->estado) }}
        </span>
    </div>
    <div class="barra" role="img" aria-label="Progreso: {{ $progreso }}%">
        <span class="barra-relleno" style="--valor: {{ $anchoBarra }}%"></span>
    </div>
    <p class="progreso-pie">
        Objetivo ${{ number_format((float) $meta->monto_objetivo, 2) }} ·
        Acumulado ${{ number_format($acumulado, 2) }} ·
        Pendiente ${{ number_format($pendiente, 2) }} ·
        {{ $progreso }}% ·
        Fecha objetivo {{ $meta->fecha_objetivo->toDateString() }}
    </p>

    @if ($aportes->isNotEmpty())
        <ul class="lista-progreso">
            @foreach ($aportes as $aporte)
                <li>
                    <div class="progreso-info">
                        <span>{{ $aporte->fecha->toDateString() }}</span>
                        <strong>${{ number_format((float) $aporte->monto, 2) }}</strong>
                    </div>
                    @if ($aporte->nota)
                        <p class="progreso-pie">{{ $aporte->nota }}</p>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif

    <div class="acciones-formulario">
        <a class="btn btn-fantasma btn-pequeno" href="{{ route('metas', ['editar' => $meta->id_meta]) }}">Editar</a>
        @if ($meta->estado !== 'cancelada')
            <form method="POST" action="{{ route('metas.update', $meta->id_meta) }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="nombre" value="{{ $meta->nombre }}">
                <input type="hidden" name="tipo_meta" value="{{ $meta->tipo_meta }}">
                <input type="hidden" name="monto_objetivo" value="{{ $meta->monto_objetivo }}">
                <input type="hidden" name="fecha_objetivo" value="{{ $meta->fecha_objetivo->toDateString() }}">
                <input type="hidden" name="estado" value="cancelada">
                <button type="submit" class="btn btn-fantasma btn-pequeno">Cancelar meta</button>
            </form>
        @endif
        @if ($aportes->isEmpty())
            <form method="POST" action="{{ route('metas.destroy', $meta->id_meta) }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-fantasma btn-pequeno">Eliminar</button>
            </form>
        @else
            <span class="campo-ayuda">Conserva el historial de aportes.</span>
        @endif
    </div>
</li>
