<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Middleware\Auth\EnsureIngresoFijoConfigurado;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas públicas de acceso
|--------------------------------------------------------------------------
| La raíz mantiene el comportamiento original: mostrar el Login.
*/
Route::get('/', [AuthController::class, 'mostrarLogin'])->name('home');
Route::get('/login', [AuthController::class, 'mostrarLogin'])->name('login');
Route::get('/registro', [AuthController::class, 'mostrarRegistro'])->name('registro');

/*
|--------------------------------------------------------------------------
| Rutas de autenticación (POST protegidas con CSRF por el grupo web)
|--------------------------------------------------------------------------
*/
Route::post('/registro', [AuthController::class, 'registrar'])->name('registro.post');
Route::post('/login', [AuthController::class, 'autenticar'])
    ->middleware('throttle:6,1')
    ->name('login.post');
Route::post('/logout', [AuthController::class, 'cerrarSesion'])->name('logout');

/*
|--------------------------------------------------------------------------
| Configuración inicial obligatoria del ingreso fijo mensual
|--------------------------------------------------------------------------
| Requiere sesión, pero no el middleware que exige el ingreso ya configurado.
*/
Route::middleware('auth')->group(function () {
    Route::get('/configuracion-inicial/ingreso', [AuthController::class, 'mostrarIngresoInicial'])
        ->name('ingreso.inicial');
    Route::post('/configuracion-inicial/ingreso', [AuthController::class, 'guardarIngresoInicial'])
        ->name('ingreso.inicial.guardar');
});

/*
|--------------------------------------------------------------------------
| Rutas privadas de los módulos
|--------------------------------------------------------------------------
| auth: exige sesión activa.
| ingreso.configurado: exige el ingreso fijo mensual configurado.
*/
Route::middleware(['auth', EnsureIngresoFijoConfigurado::class])->group(function () {
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
});
