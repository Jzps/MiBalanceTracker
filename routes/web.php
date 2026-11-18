<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DeudaController;
use App\Http\Controllers\MetaController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\MovimientoController;
use App\Http\Controllers\PresupuestoController;
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

    // Movimientos y categorías (la administración de categorías vive en la vista de movimientos).
    Route::get('/movimientos', [MovimientoController::class, 'index'])->name('movimientos');
    Route::post('/movimientos', [MovimientoController::class, 'store'])->name('movimientos.store');
    Route::put('/movimientos/{movimiento}', [MovimientoController::class, 'update'])->name('movimientos.update');
    Route::delete('/movimientos/{movimiento}', [MovimientoController::class, 'destroy'])->name('movimientos.destroy');

    Route::post('/categorias', [CategoriaController::class, 'store'])->name('categorias.store');
    Route::put('/categorias/{categoria}', [CategoriaController::class, 'update'])->name('categorias.update');
    Route::delete('/categorias/{categoria}', [CategoriaController::class, 'destroy'])->name('categorias.destroy');

    // Presupuestos.
    Route::get('/presupuestos', [PresupuestoController::class, 'index'])->name('presupuestos');
    Route::post('/presupuestos', [PresupuestoController::class, 'store'])->name('presupuestos.store');
    Route::put('/presupuestos/{presupuesto}', [PresupuestoController::class, 'update'])->name('presupuestos.update');
    Route::delete('/presupuestos/{presupuesto}', [PresupuestoController::class, 'destroy'])->name('presupuestos.destroy');

    // Metas, fondo de emergencia y aportes de ahorro.
    Route::get('/metas', [MetaController::class, 'index'])->name('metas');
    Route::post('/metas', [MetaController::class, 'store'])->name('metas.store');
    Route::put('/metas/{meta}', [MetaController::class, 'update'])->name('metas.update');
    Route::delete('/metas/{meta}', [MetaController::class, 'destroy'])->name('metas.destroy');
    Route::post('/metas/{meta}/aportes', [MetaController::class, 'storeAporte'])->name('aportes.store');

    // Deudas y pagos.
    Route::get('/deudas', [DeudaController::class, 'index'])->name('deudas');
    Route::post('/deudas', [DeudaController::class, 'store'])->name('deudas.store');
    Route::put('/deudas/{deuda}', [DeudaController::class, 'update'])->name('deudas.update');
    Route::delete('/deudas/{deuda}', [DeudaController::class, 'destroy'])->name('deudas.destroy');
    Route::post('/deudas/{deuda}/pagos', [DeudaController::class, 'storePago'])->name('pagos.store');

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
