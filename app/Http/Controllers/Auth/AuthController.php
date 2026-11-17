<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Determina a dónde debe ir un usuario autenticado según su configuración inicial.
     */
    private function destinoAutenticado(): string
    {
        $usuario = Auth::user();

        if ($usuario && (is_null($usuario->ingreso_fijo_mensual) || (float) $usuario->ingreso_fijo_mensual <= 0)) {
            return route('ingreso.inicial');
        }

        return route('dashboard');
    }

    public function mostrarLogin()
    {
        if (Auth::check()) {
            return redirect($this->destinoAutenticado());
        }

        return view('auth.login', [
            'tituloPagina' => 'Iniciar sesión',
            'paginaActiva' => '',
            'mostrarNavbar' => false,
            'claseBody' => 'pagina-auth',
        ]);
    }

    public function mostrarRegistro()
    {
        if (Auth::check()) {
            return redirect($this->destinoAutenticado());
        }

        return view('auth.registro', [
            'tituloPagina' => 'Crear cuenta',
            'paginaActiva' => '',
            'mostrarNavbar' => false,
            'claseBody' => 'pagina-auth',
        ]);
    }

    public function registrar(Request $request)
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'correo' => ['required', 'email', 'max:100', 'unique:usuario,correo'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'correo.required' => 'El correo es obligatorio.',
            'correo.email' => 'El correo debe tener un formato válido.',
            'correo.max' => 'El correo no puede superar los 100 caracteres.',
            'correo.unique' => 'Ese correo ya está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
        ]);

        $usuario = Usuario::create([
            'nombre' => $datos['nombre'],
            'correo' => $datos['correo'],
            'password_hash' => Hash::make($datos['password']),
        ]);

        Auth::login($usuario);
        $request->session()->regenerate();

        return redirect()->route('ingreso.inicial');
    }

    public function autenticar(Request $request)
    {
        $credenciales = $request->validate([
            'correo' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'correo.required' => 'El correo es obligatorio.',
            'correo.email' => 'El correo debe tener un formato válido.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        if (! Auth::attempt($credenciales, false)) {
            return back()
                ->withInput($request->only('correo'))
                ->withErrors(['correo' => 'Correo o contraseña incorrectos.']);
        }

        $request->session()->regenerate();

        if ($this->esIngresoPendiente(Auth::user())) {
            return redirect()->route('ingreso.inicial');
        }

        return redirect()->intended(route('dashboard'));
    }

    public function cerrarSesion(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function mostrarIngresoInicial()
    {
        $usuario = Auth::user();

        if (! $this->esIngresoPendiente($usuario)) {
            return redirect()->route('dashboard');
        }

        return view('auth.ingreso-inicial', [
            'tituloPagina' => 'Configuración inicial',
            'paginaActiva' => '',
        ]);
    }

    public function guardarIngresoInicial(Request $request)
    {
        $datos = $request->validate([
            'ingreso_fijo_mensual' => ['required', 'numeric', 'gt:0', 'decimal:0,2', 'max:99999999.99'],
        ], [
            'ingreso_fijo_mensual.required' => 'El ingreso fijo mensual es obligatorio.',
            'ingreso_fijo_mensual.numeric' => 'El ingreso debe ser un valor numérico.',
            'ingreso_fijo_mensual.gt' => 'El ingreso debe ser mayor que cero.',
            'ingreso_fijo_mensual.decimal' => 'El ingreso puede tener hasta dos decimales.',
            'ingreso_fijo_mensual.max' => 'El ingreso excede el valor permitido.',
        ]);

        $usuario = Auth::user();
        $usuario->ingreso_fijo_mensual = $datos['ingreso_fijo_mensual'];
        $usuario->save();

        return redirect()->route('dashboard');
    }

    private function esIngresoPendiente(?Usuario $usuario): bool
    {
        return is_null($usuario) || is_null($usuario->ingreso_fijo_mensual) || (float) $usuario->ingreso_fijo_mensual <= 0;
    }
}
