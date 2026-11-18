@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/pages/metas.css') }}">
@endpush

@section('content')
<section class="modulo-cabecera">
    <div>
        <h1>Metas y fondo de emergencia</h1>
        <p>Define objetivos de ahorro, registra aportes y sigue tu progreso.</p>
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
    <div>
        <!-- RF-13: creación de metas de ahorro -->
        <section class="tarjeta">
            <h2>{{ $metaEditar ? 'Editar meta' : 'Nueva meta' }}</h2>
            <form class="formulario" action="{{ $metaEditar ? route('metas.update', $metaEditar->id_meta) : route('metas.store') }}" method="post">
                @csrf
                @if ($metaEditar)
                    @method('PUT')
                @endif

                <div class="campo">
                    <label for="tipo_meta">Tipo *</label>
                    <select id="tipo_meta" name="tipo_meta" required>
                        <option value="meta_ahorro" @selected(old('tipo_meta', $metaEditar->tipo_meta ?? 'meta_ahorro') === 'meta_ahorro')>Meta de ahorro</option>
                        <option value="fondo_emergencia" @selected(old('tipo_meta', $metaEditar->tipo_meta ?? '') === 'fondo_emergencia')>Fondo de emergencia</option>
                    </select>
                </div>

                <div class="campo">
                    <label for="nombre">Nombre de la meta *</label>
                    <input type="text" id="nombre" name="nombre" required maxlength="100"
                           value="{{ old('nombre', $metaEditar->nombre ?? '') }}" placeholder=" ">
                </div>

                <div class="rejilla rejilla-2 rejilla-ajustada">
                    <div class="campo">
                        <label for="monto_objetivo">Monto objetivo *</label>
                        <div class="campo-moneda">
                            <span aria-hidden="true">$</span>
                            <input type="number" id="monto_objetivo" name="monto_objetivo" min="0.01" step="0.01" max="99999999.99" required
                                   value="{{ old('monto_objetivo', $metaEditar->monto_objetivo ?? '') }}"
                                   inputmode="decimal" placeholder=" ">
                        </div>
                        @error('monto_objetivo')<span class="campo-ayuda">{{ $message }}</span>@enderror
                    </div>
                    <div class="campo">
                        <label for="fecha_objetivo">Fecha objetivo *</label>
                        <input type="date" id="fecha_objetivo" name="fecha_objetivo" required
                               value="{{ old('fecha_objetivo', $metaEditar?->fecha_objetivo?->toDateString() ?? '') }}">
                    </div>
                </div>

                @if ($metaEditar)
                    <div class="campo">
                        <label for="estado">Estado</label>
                        <select id="estado" name="estado">
                            <option value="activa" @selected(old('estado', $metaEditar->estado) === 'activa')>Activa</option>
                            <option value="pausada" @selected(old('estado', $metaEditar->estado) === 'pausada')>Pausada</option>
                            <option value="cancelada" @selected(old('estado', $metaEditar->estado) === 'cancelada')>Cancelada</option>
                        </select>
                        <p class="campo-ayuda">El estado "completada" se determina con los aportes registrados.</p>
                    </div>
                @endif

                <div class="acciones-formulario">
                    @if ($metaEditar)
                        <a class="btn btn-secundario" href="{{ route('metas') }}">Cancelar edición</a>
                    @endif
                    <button type="submit" class="btn btn-primario">{{ $metaEditar ? 'Actualizar meta' : 'Crear meta' }}</button>
                </div>
            </form>
        </section>

        <!-- RF-15: aportes a metas y fondo de emergencia -->
        <section class="tarjeta">
            <h2>Registrar aporte</h2>
            @php $primeraMeta = $metas->first()['meta'] ?? null; @endphp
            <form id="frm-aporte" class="formulario" method="post"
                  action="{{ $primeraMeta ? route('aportes.store', $primeraMeta->id_meta) : route('metas') }}"
                  data-base="{{ route('aportes.store', ['meta' => '__META__']) }}">
                @csrf

                <div class="campo">
                    <label for="id_meta">Meta *</label>
                    <select id="id_meta" name="id_meta" required>
                        <option value="">Selecciona...</option>
                        @foreach ($metas as $item)
                            @if ($item['meta']->estado === 'activa')
                                <option value="{{ $item['meta']->id_meta }}">{{ $item['meta']->nombre }}</option>
                            @endif
                        @endforeach
                    </select>
                    @if ($metas->isEmpty())
                        <p class="campo-ayuda">Crea una meta antes de registrar aportes.</p>
                    @endif
                </div>

                <div class="rejilla rejilla-2 rejilla-ajustada">
                    <div class="campo">
                        <label for="monto_aporte">Monto del aporte *</label>
                        <div class="campo-moneda">
                            <span aria-hidden="true">$</span>
                            <input type="number" id="monto_aporte" name="monto" min="0.01" step="0.01" max="99999999.99" required
                                   value="{{ old('monto') }}" inputmode="decimal" placeholder=" ">
                        </div>
                        @error('monto')<span class="campo-ayuda">{{ $message }}</span>@enderror
                    </div>
                    <div class="campo">
                        <label for="fecha_aporte">Fecha *</label>
                        <input type="date" id="fecha_aporte" name="fecha" required value="{{ old('fecha') }}">
                    </div>
                </div>

                <div class="campo">
                    <label for="nota_aporte">Nota</label>
                    <input type="text" id="nota_aporte" name="nota" maxlength="255" value="{{ old('nota') }}" placeholder=" ">
                </div>

                <div class="acciones-formulario">
                    <button type="submit" class="btn btn-primario">Registrar aporte</button>
                </div>
            </form>
        </section>
    </div>

    <div>
        <!-- RF-14: gestión del fondo de emergencia -->
        <section class="tarjeta tarjeta-destacada">
            <h2>Fondo de emergencia</h2>
            <ul class="lista-progreso">
                @foreach ($metas as $item)
                    @if ($item['meta']->tipo_meta === 'fondo_emergencia')
                        @include('pages.metas._progreso', $item)
                    @endif
                @endforeach
            </ul>
            @if ($metas->where('meta.tipo_meta', 'fondo_emergencia')->isEmpty())
                <div class="estado-vacio">
                    <h3>Sin fondo configurado</h3>
                    <p>Establece el monto objetivo y la fecha del fondo de emergencia.</p>
                </div>
            @endif
        </section>

        <!-- RF-16: progreso de las metas -->
        <section class="tarjeta">
            <h2>Progreso de mis metas</h2>
            <ul class="lista-progreso">
                @foreach ($metas as $item)
                    @if ($item['meta']->tipo_meta === 'meta_ahorro')
                        @include('pages.metas._progreso', $item)
                    @endif
                @endforeach
            </ul>
            @if ($metas->where('meta.tipo_meta', 'meta_ahorro')->isEmpty())
                <div class="estado-vacio">
                    <h3>Sin metas</h3>
                    <p>Aún no tienes metas de ahorro. Crea la primera con el formulario.</p>
                </div>
            @endif
        </section>
    </div>
</div>
@endsection
