<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    // Roles que pueden acceder al panel Blade
    private const ROLES_PANEL = ['administrador', 'medico', 'gestor_citas'];

    public function showLogin(Request $request)
    {
        if (Auth::check()) {
            return $this->redirigirSegunRol(Auth::user()->rol?->nombre);
        }

        // Detectar empresa por parámetro (para acceso específico de IPS)
        // Si no hay parámetro, muestra branding por defecto (modo demo)
        $empresa = null;

        if ($request->filled('empresa')) {
            $empresa = Empresa::where('id', $request->input('empresa'))
                ->where('activo', true)
                ->first();
        } elseif ($request->filled('nit')) {
            $empresa = Empresa::where('nit', $request->input('nit'))
                ->where('activo', true)
                ->first();
        }

        // Guardar empresa en sesión para mantener branding durante el proceso de login
        if ($empresa) {
            session(['login_empresa_id' => $empresa->id]);
        } else {
            // Si no hay parámetro, usar la de sesión si existe (ej: vino del onboarding)
            $empresaId = session('login_empresa_id');
            if ($empresaId) {
                $empresa = Empresa::where('id', $empresaId)->where('activo', true)->first();
            }
        }

        return view('admin.auth.login', compact('empresa'));
    }

    public function login(Request $request)
    {
        $request->validate([
            'login'        => ['required', 'string'],
            'password'     => ['required', 'string'],
            'tipo_usuario' => ['required', 'in:afiliado,empleador'],
        ]);

        $loginValue = $request->login;
        $field = filter_var($loginValue, FILTER_VALIDATE_EMAIL) ? 'email' : 'identificacion';

        if (!Auth::attempt(
            [$field => $loginValue, 'password' => $request->password],
            $request->boolean('remember')
        )) {
            return back()
                ->withErrors(['login' => 'Credenciales incorrectas. Verifica tu documento o correo y contraseña.'])
                ->withInput();
        }

        $user = Auth::user();

        if (!$user->activo) {
            Auth::logout();
            return back()
                ->withErrors(['email' => 'Tu cuenta está desactivada. Contacta al administrador.'])
                ->withInput();
        }

        $rol = $user->rol?->nombre;
        $tipoUsuario = $request->input('tipo_usuario');

        // Validar rol según el tab seleccionado
        if ($tipoUsuario === 'afiliado') {
            // Solo pacientes pueden entrar por el tab de Afiliados
            if ($rol !== 'paciente') {
                Auth::logout();
                return back()
                    ->withErrors(['login' => 'Este acceso es solo para pacientes. Los empleadores deben usar el tab "Empleadores".'])
                    ->withInput();
            }
        } elseif ($tipoUsuario === 'empleador') {
            // Solo empleadores (admin, médico, gestor) pueden entrar por el tab de Empleadores
            if (!in_array($rol, self::ROLES_PANEL)) {
                Auth::logout();
                return back()
                    ->withErrors(['login' => 'Este acceso es solo para personal de la IPS. Los pacientes deben usar el tab "Afiliados".'])
                    ->withInput();
            }
        }

        $request->session()->regenerate();

        return $this->redirigirSegunRol($rol);
    }

    private function redirigirSegunRol(?string $rol)
    {
        return match($rol) {
            'paciente'     => redirect()->route('paciente.dashboard'),
            'medico'       => redirect()->route('medico.dashboard'),
            'administrador' => redirect()->route('admin.dashboard'),
            'gestor_citas' => redirect()->route('gestor.dashboard'),
            default        => redirect()->route('gestor.dashboard'),
        };
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
