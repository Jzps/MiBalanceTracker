@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/pages/movimientos.css') }}">
@endpush

@section('content')
<section class="modulo-cabecera">
    <div>
        <h1>Movimientos</h1>
        <p>Registra tus ingresos y gastos, y consulta el historial de tu dinero.</p>
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
    <!-- RF-05: registro de ingresos y gastos -->
    <section class="tarjeta">
        <h2>{{ $movimientoEditar ? 'Editar movimiento' : 'Nuevo movimiento' }}</h2>
        <form class="formulario" action="{{ $movimientoEditar ? route('movimientos.update', $movimientoEditar->id_movimiento) : route('movimientos.store') }}" method="post">
            @csrf
            @if ($movimientoEditar)
                @method('PUT')
            @endif

            <div class="rejilla rejilla-2 rejilla-ajustada">
                <div class="campo">
                    <label for="tipo">Tipo de movimiento *</label>
                    <select id="tipo" name="tipo" required>
                        <option value="">Selecciona...</option>
                        <option value="ingreso" @selected(old('tipo', $movimientoEditar->tipo ?? '') === 'ingreso')>Ingreso</option>
                        <option value="gasto" @selected(old('tipo', $movimientoEditar->tipo ?? '') === 'gasto')>Gasto</option>
                    </select>
                    @error('tipo')<span class="campo-ayuda">{{ $message }}</span>@enderror
                </div>

                <div class="campo">
                    <label for="id_categoria">Categoría *</label>
                    <select id="id_categoria" name="id_categoria" required>
                        <option value="">Selecciona...</option>
                        @foreach ($categorias as $categoria)
                            <option value="{{ $categoria->id_categoria }}"
                                    data-tipo="{{ $categoria->tipo_movimiento }}"
                                    @selected((string) old('id_categoria', $movimientoEditar->id_categoria ?? '') === (string) $categoria->id_categoria)>
                                {{ $categoria->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_categoria')<span class="campo-ayuda">{{ $message }}</span>@enderror
                    @if ($categorias->isEmpty())
                        <p class="campo-ayuda">Crea una categoría activa antes de registrar movimientos.</p>
                    @endif
                </div>

                <div class="campo campo-ancho">
                    <label for="descripcion">Descripción *</label>
                    <input type="text" id="descripcion" name="descripcion" required maxlength="150"
                           value="{{ old('descripcion', $movimientoEditar->descripcion ?? '') }}" placeholder=" ">
                    @error('descripcion')<span class="campo-ayuda">{{ $message }}</span>@enderror
                </div>

                <div class="campo">
                    <label for="monto">Monto *</label>
                    <div class="campo-moneda">
                        <span aria-hidden="true">$</span>
                        <input type="number" id="monto" name="monto" min="0.01" step="0.01" max="99999999.99" required
                               value="{{ old('monto', $movimientoEditar->monto ?? '') }}"
                               inputmode="decimal" placeholder=" ">
                    </div>
                    @error('monto')<span class="campo-ayuda">{{ $message }}</span>@enderror
                </div>

                <div class="campo">
                    <label for="fecha">Fecha *</label>
                    <input type="date" id="fecha" name="fecha" required
                           value="{{ old('fecha', $movimientoEditar?->fecha?->toDateString() ?? '') }}">
                    @error('fecha')<span class="campo-ayuda">{{ $message }}</span>@enderror
                </div>

                <div class="campo">
                    <label for="metodo_pago">Método de pago</label>
                    <select id="metodo_pago" name="metodo_pago">
                        @php $metodo = old('metodo_pago', $movimientoEditar->metodo_pago ?? ''); @endphp
                        <option value="">No aplica</option>
                        <option value="efectivo" @selected($metodo === 'efectivo')>Efectivo</option>
                        <option value="tarjeta_debito" @selected($metodo === 'tarjeta_debito')>Tarjeta de débito</option>
                        <option value="tarjeta_credito" @selected($metodo === 'tarjeta_credito')>Tarjeta de crédito</option>
                        <option value="transferencia" @selected($metodo === 'transferencia')>Transferencia</option>
                    </select>
                </div>

                <div class="campo campo-ancho">
                    <label for="notas">Notas opcionales</label>
                    <textarea id="notas" name="notas" rows="2" maxlength="500" placeholder=" ">{{ old('notas', $movimientoEditar->notas ?? '') }}</textarea>
                </div>
            </div>

            <div class="acciones-formulario">
                <button type="reset" class="btn btn-fantasma">Limpiar</button>
                @if ($movimientoEditar)
                    <a class="btn btn-secundario" href="{{ route('movimientos') }}">Cancelar edición</a>
                @endif
                <button type="submit" class="btn btn-primario">{{ $movimientoEditar ? 'Actualizar movimiento' : 'Guardar movimiento' }}</button>
            </div>
        </form>
    </section>

    <!-- RF-06: consulta del historial -->
    <section class="tarjeta">
        <h2>Historial</h2>
        <form class="filtros" id="filtros-movimientos" method="GET" action="{{ route('movimientos') }}">
            <div class="campo">
                <label for="filtro-buscar">Buscar</label>
                <input type="search" id="filtro-buscar" name="buscar" value="{{ request('buscar') }}" placeholder=" ">
            </div>
            <div class="campo">
                <label for="filtro-tipo">Tipo</label>
                <select id="filtro-tipo" name="tipo">
                    <option value="">Todos</option>
                    <option value="ingreso" @selected(request('tipo') === 'ingreso')>Ingresos</option>
                    <option value="gasto" @selected(request('tipo') === 'gasto')>Gastos</option>
                </select>
            </div>
            <div class="campo">
                <label for="filtro-mes">Mes</label>
                <input type="month" id="filtro-mes" name="mes" value="{{ request('mes') }}">
            </div>
        </form>

        <div class="tabla-contenedor">
            <table class="tabla" id="tabla-movimientos">
                <thead>
                    <tr>
                        <th scope="col">Fecha</th>
                        <th scope="col">Tipo</th>
                        <th scope="col">Categoría</th>
                        <th scope="col">Descripción</th>
                        <th scope="col">Método</th>
                        <th scope="col" class="num">Monto</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($movimientos as $movimiento)
                        <tr>
                            <td>{{ $movimiento->fecha->toDateString() }}</td>
                            <td>{{ ucfirst($movimiento->tipo) }}</td>
                            <td>{{ $movimiento->categoria->nombre ?? '—' }}</td>
                            <td>{{ $movimiento->descripcion }}</td>
                            <td>{{ $movimiento->metodo_pago ?? '—' }}</td>
                            <td class="num">{{ $movimiento->monto }}</td>
                            <td>
                                <a class="btn btn-fantasma btn-pequeno" href="{{ route('movimientos', ['editar' => $movimiento->id_movimiento]) }}">Editar</a>
                                <form method="POST" action="{{ route('movimientos.destroy', $movimiento->id_movimiento) }}" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-fantasma btn-pequeno">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($movimientos->isEmpty())
            <div class="estado-vacio">
                <h3>Sin movimientos</h3>
                <p>Sin movimientos registrados. Registra uno con el formulario.</p>
            </div>
        @endif
    </section>
</div>

<!-- RF-09: administración de categorías integrada en Movimientos -->
<section class="tarjeta">
    <div class="tarjeta-cabecera">
        <h2>Mis categorías</h2>
    </div>

    <form class="formulario" action="{{ $categoriaEditar ? route('categorias.update', $categoriaEditar->id_categoria) : route('categorias.store') }}" method="post">
        @csrf
        @if ($categoriaEditar)
            @method('PUT')
        @endif
        <div class="rejilla rejilla-3 rejilla-ajustada">
            <div class="campo">
                <label for="nombre_categoria">Nombre *</label>
                <input type="text" id="nombre_categoria" name="nombre" required maxlength="80"
                       value="{{ old('nombre', $categoriaEditar->nombre ?? '') }}" placeholder=" ">
            </div>
            <div class="campo">
                <label for="tipo_categoria">Tipo *</label>
                <select id="tipo_categoria" name="tipo_movimiento" required>
                    <option value="">Selecciona...</option>
                    <option value="ingreso" @selected(old('tipo_movimiento', $categoriaEditar->tipo_movimiento ?? '') === 'ingreso')>Ingreso</option>
                    <option value="gasto" @selected(old('tipo_movimiento', $categoriaEditar->tipo_movimiento ?? '') === 'gasto')>Gasto</option>
                </select>
            </div>
            <div class="campo">
                <label for="clasificacion">Clasificación 50/30/20 *</label>
                <select id="clasificacion" name="clasificacion_503020" required>
                    <option value="">Selecciona...</option>
                    <option value="necesidad" @selected(old('clasificacion_503020', $categoriaEditar->clasificacion_503020 ?? '') === 'necesidad')>Necesidad</option>
                    <option value="deseo" @selected(old('clasificacion_503020', $categoriaEditar->clasificacion_503020 ?? '') === 'deseo')>Deseo</option>
                    <option value="ahorro" @selected(old('clasificacion_503020', $categoriaEditar->clasificacion_503020 ?? '') === 'ahorro')>Ahorro</option>
                    <option value="no_aplica" @selected(old('clasificacion_503020', $categoriaEditar->clasificacion_503020 ?? '') === 'no_aplica')>No aplica</option>
                </select>
            </div>
        </div>
        <div class="acciones-formulario">
            @if ($categoriaEditar)
                <a class="btn btn-secundario" href="{{ route('movimientos') }}">Cancelar edición</a>
            @endif
            <button type="submit" class="btn btn-primario">{{ $categoriaEditar ? 'Actualizar categoría' : 'Crear categoría' }}</button>
        </div>
    </form>

    <div class="tabla-contenedor">
        <table class="tabla">
            <thead>
                <tr>
                    <th scope="col">Nombre</th>
                    <th scope="col">Tipo</th>
                    <th scope="col">Clasificación</th>
                    <th scope="col">Estado</th>
                    <th scope="col">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($categoriasTodas as $categoria)
                    <tr>
                        <td>{{ $categoria->nombre }}</td>
                        <td>{{ ucfirst($categoria->tipo_movimiento) }}</td>
                        <td>{{ $categoria->clasificacion_503020 }}</td>
                        <td>
                            <span class="etiqueta {{ $categoria->activa ? 'etiqueta-exito' : 'etiqueta-alerta' }}">
                                {{ $categoria->activa ? 'Activa' : 'Inactiva' }}
                            </span>
                        </td>
                        <td>
                            <a class="btn btn-fantasma btn-pequeno" href="{{ route('movimientos', ['editar_categoria' => $categoria->id_categoria]) }}">Editar</a>
                            @if ($categoria->activa)
                                <form method="POST" action="{{ route('categorias.destroy', $categoria->id_categoria) }}" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-fantasma btn-pequeno">Desactivar</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('categorias.update', $categoria->id_categoria) }}" style="display: inline;">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="nombre" value="{{ $categoria->nombre }}">
                                    <input type="hidden" name="tipo_movimiento" value="{{ $categoria->tipo_movimiento }}">
                                    <input type="hidden" name="clasificacion_503020" value="{{ $categoria->clasificacion_503020 }}">
                                    <input type="hidden" name="activa" value="1">
                                    <button type="submit" class="btn btn-fantasma btn-pequeno">Activar</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
@endsection
