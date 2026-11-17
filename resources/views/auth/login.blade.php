@extends('layouts.app')

@section('content')
<section class="auth-caja">
    <div class="auth-cabecera">
        <img src="{{ asset('assets/img/logo.svg') }}" alt="Logotipo de Finanzas Personales" width="56" height="56">
        <h1>Iniciar sesión</h1>
        <p>Acceso al sistema de finanzas personales.</p>
    </div>

    @if ($errors->any())
        <div id="mensaje" class="alerta alerta-error" role="alert">
            @foreach ($errors->all() as $error)
                {{ $error }}@if (!$loop->last)<br>@endif
            @endforeach
        </div>
    @else
        <div id="mensaje" class="alerta alerta-error" role="alert" hidden></div>
    @endif

    <form id="frm-login" class="formulario" action="{{ route('login.post') }}" method="post">
        @csrf
        <div class="campo">
            <label for="usuario">Correo electrónico</label>
            <input type="email" id="usuario" name="correo" value="{{ old('correo') }}" required
                   autocomplete="username" placeholder=" ">
        </div>

        <div class="campo">
            <label for="password">Contraseña</label>
            <div class="campo-con-boton">
                <input type="password" id="password" name="password" required autocomplete="current-password" placeholder=" ">
                <button type="button" class="btn btn-fantasma" data-mostrar-password="password"
                        aria-label="Mostrar contraseña" aria-pressed="false">Ver</button>
            </div>
        </div>

        <button type="submit" class="btn btn-primario btn-block">Ingresar</button>
    </form>

    <p class="auth-enlace">
        ¿No tienes cuenta? <a href="{{ route('registro') }}">Regístrate</a>
    </p>
</section>
@endsection
