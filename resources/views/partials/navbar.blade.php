<header class="barra-superior">
    <a class="marca" href="{{ route('dashboard') }}" aria-label="Ir al Dashboard">
        <img src="{{ asset('assets/img/logo.svg') }}" alt="" width="34" height="34">
        <span class="marca-texto">Mi <strong>BalanceTracker</strong></span>
    </a>

    <div class="barra-acciones">
        <a class="btn btn-fantasma btn-pequeno" href="{{ route('login') }}">Cerrar sesión</a>
        <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="menu-principal">
            <span class="nav-toggle-barra"></span>
            <span class="nav-toggle-barra"></span>
            <span class="nav-toggle-barra"></span>
            <span class="visually-hidden">Abrir o cerrar el menú</span>
        </button>
    </div>
</header>

<aside class="lateral">
    <nav id="menu-principal" class="menu-principal" aria-label="Módulos del sistema">
        <ul>
            @foreach ($modulos as $clave => $modulo)
                @php $activo = ($clave === ($paginaActiva ?? '')); @endphp
                <li>
                    <a href="{{ route($modulo['ruta']) }}"
                       class="{{ $activo ? 'activo' : '' }}"
                       @if($activo) aria-current="page" @endif>
                        <svg class="nav-ico" viewBox="0 0 24 24" aria-hidden="true">{!! $modulo['icono'] !!}</svg>
                        <span>{{ $modulo['titulo'] }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>
</aside>
