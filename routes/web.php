<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('pages.login', [
        'tituloPagina' => 'Iniciar sesión',
        'paginaActiva' => '',
        'mostrarNavbar' => false,
        'claseBody' => 'pagina-auth',
    ]);
})->name('home');

Route::get('/login', function () {
    return view('pages.login', [
        'tituloPagina' => 'Iniciar sesión',
        'paginaActiva' => '',
        'mostrarNavbar' => false,
        'claseBody' => 'pagina-auth',
    ]);
})->name('login');

Route::get('/registro', function () {
    return view('pages.registro', [
        'tituloPagina' => 'Crear cuenta',
        'paginaActiva' => '',
        'mostrarNavbar' => false,
        'claseBody' => 'pagina-auth',
    ]);
})->name('registro');

Route::get('/dashboard', function () {
    return view('pages.dashboard.index', [
        'tituloPagina' => 'Dashboard',
        'paginaActiva' => 'dashboard',
    ]);
})->name('dashboard');

Route::get('/movimientos', function () {
    return view('pages.movimientos.index', [
        'tituloPagina' => 'Movimientos',
        'paginaActiva' => 'movimientos',
    ]);
})->name('movimientos');

Route::get('/presupuestos', function () {
    return view('pages.presupuestos.index', [
        'tituloPagina' => 'Presupuestos',
        'paginaActiva' => 'presupuestos',
    ]);
})->name('presupuestos');

Route::get('/metas', function () {
    return view('pages.metas.index', [
        'tituloPagina' => 'Metas',
        'paginaActiva' => 'metas',
    ]);
})->name('metas');

Route::get('/deudas', function () {
    return view('pages.deudas.index', [
        'tituloPagina' => 'Deudas',
        'paginaActiva' => 'deudas',
    ]);
})->name('deudas');

Route::get('/analisis', function () {
    return view('pages.analisis.index', [
        'tituloPagina' => 'Análisis',
        'paginaActiva' => 'analisis',
    ]);
})->name('analisis');

Route::get('/educacion', function () {
    return view('pages.educacion.index', [
        'tituloPagina' => 'Educación financiera',
        'paginaActiva' => 'educacion',
    ]);
})->name('educacion');

Route::get('/distribucion', function () {
    return view('pages.distribucion.index', [
        'tituloPagina' => '50/30/20',
        'paginaActiva' => 'distribucion',
    ]);
})->name('distribucion');

Route::get('/configuracion', function () {
    return view('pages.configuracion.index', [
        'tituloPagina' => 'Configuración',
        'paginaActiva' => 'configuracion',
    ]);
})->name('configuracion');
