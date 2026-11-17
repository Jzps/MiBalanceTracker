@extends('layouts.app')

@section('content')
<section class="auth-caja">
    <div class="auth-cabecera">
        <img src="{{ asset('assets/img/logo.svg') }}" alt="Logotipo de Finanzas Personales" width="56" height="56">
        <h1>Configuración inicial</h1>
        <p>Registra tu ingreso fijo mensual para continuar.</p>
    </div>

    @if ($errors->any())
        <div class="alerta alerta-error" role="alert">
            @foreach ($errors->all() as $error)
                {{ $error }}@if (!$loop->last)<br>@endif
            @endforeach
        </div>
    @endif

    <form id="frm-ingreso-inicial" class="formulario" action="{{ route('ingreso.inicial.guardar') }}" method="post">
        @csrf
        <div class="campo">
            <label for="ingreso_fijo_mensual">Ingreso fijo mensual</label>
            <div class="campo-moneda">
                <span aria-hidden="true">$</span>
                <input type="number" id="ingreso_fijo_mensual" name="ingreso_fijo_mensual"
                       value="{{ old('ingreso_fijo_mensual') }}" min="0.01" step="0.01" max="99999999.99"
                       required inputmode="decimal" placeholder=" ">
            </div>
            <p class="campo-ayuda">Este dato es obligatorio y será la referencia inicial del sistema.</p>
        </div>

        <button type="submit" class="btn btn-primario btn-block">Guardar ingreso</button>
    </form>
</section>
@endsection
