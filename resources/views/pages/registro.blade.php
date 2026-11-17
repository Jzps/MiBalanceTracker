@extends('layouts.app')

@section('content')
<section class="auth-caja">
    <div class="auth-cabecera">
        <img src="{{ asset('assets/img/logo.svg') }}" alt="Logotipo de Finanzas Personales" width="56" height="56">
        <h1>Crear cuenta</h1>
        <p>Formulario de registro del sistema de finanzas personales.</p>
    </div>

    <form class="formulario" action="{{ route('registro') }}" method="post">
        <div class="campo">
            <label for="nombre">Nombre completo</label>
            <input type="text" id="nombre" name="nombre" required maxlength="100"
                   autocomplete="name" placeholder=" ">
        </div>

        <div class="campo">
            <label for="correo">Correo electrónico</label>
            <input type="email" id="correo" name="correo" required maxlength="120"
                   autocomplete="email" placeholder=" ">
        </div>

        <div class="campo">
            <label for="password">Contraseña</label>
            <div class="campo-con-boton">
                <input type="password" id="password" name="password" required minlength="6"
                       autocomplete="new-password" placeholder=" ">
                <button type="button" class="btn btn-fantasma" data-mostrar-password="password"
                        aria-label="Mostrar contraseña" aria-pressed="false">Ver</button>
            </div>
            <p class="campo-ayuda">Mínimo 6 caracteres.</p>
        </div>

        <div class="campo">
            <label for="confirmar_password">Confirmar contraseña</label>
            <div class="campo-con-boton">
                <input type="password" id="confirmar_password" name="confirmar_password" required minlength="6"
                       autocomplete="new-password" placeholder=" ">
                <button type="button" class="btn btn-fantasma" data-mostrar-password="confirmar_password"
                        aria-label="Mostrar contraseña de confirmación" aria-pressed="false">Ver</button>
            </div>
        </div>

        <button type="submit" class="btn btn-primario btn-block">Crear cuenta</button>
    </form>

    <p class="auth-enlace">
        ¿Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesión</a>
    </p>
</section>
@endsection
