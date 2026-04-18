<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\BienvenidaEmpleadorMail;
use App\Mail\SolicitudRechazadaMail;
use App\Models\Empresa;
use App\Models\Rol;
use App\Models\SolicitudEmpleador;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SolicitudEmpleadorController extends Controller
{
    // ── Lista de solicitudes para el panel admin ─────────────────────────
    public function index(): View
    {
        $empresa = auth()->user()->empresa;

        $solicitudes = SolicitudEmpleador::where('empresa_id', $empresa->id)
            ->where('estado', 'pendiente')
            ->latest()
            ->get()
            ->groupBy('rol_solicitado');

        $aprobados = SolicitudEmpleador::where('empresa_id', $empresa->id)
            ->where('estado', 'aprobado')
            ->latest()->take(20)->get();

        $rechazados = SolicitudEmpleador::where('empresa_id', $empresa->id)
            ->where('estado', 'rechazado')
            ->latest()->take(20)->get();

        return view('admin.solicitudes.index', compact('solicitudes', 'aprobados', 'rechazados', 'empresa'));
    }

    // ── Aprobar: crear usuario en el sistema ─────────────────────────────
    public function aprobar(SolicitudEmpleador $solicitud): RedirectResponse
    {
        $this->authorize('update', auth()->user()->empresa);

        if ($solicitud->estado !== 'pendiente') {
            return back()->with('error', 'Esta solicitud ya fue procesada.');
        }

        // Verificar que el correo no exista ya en la tabla users
        if (User::where('email', $solicitud->correo)
                ->where('empresa_id', $solicitud->empresa_id)
                ->exists()) {
            return back()->with('error', 'No se puede aprobar: el correo ' . $solicitud->correo . ' ya está registrado en el sistema. El usuario debe usar la opción de recuperar contraseña.');
        }

        // Verificar que la identificación no exista ya
        if (User::where('identificacion', $solicitud->numero_documento)
                ->where('empresa_id', $solicitud->empresa_id)
                ->exists()) {
            return back()->with('error', 'No se puede aprobar: ya existe un usuario con el número de documento ' . $solicitud->numero_documento . '.');
        }

        $empresa = $solicitud->empresa;

        DB::transaction(function () use ($solicitud, $empresa) {
            $rol = Rol::where('nombre', $solicitud->rol_solicitado)->firstOrFail();

            User::create([
                'empresa_id'      => $solicitud->empresa_id,
                'rol_id'          => $rol->id,
                'nombre'          => $solicitud->nombres . ' ' . $solicitud->apellidos,
                'email'           => $solicitud->correo,
                'identificacion'  => $solicitud->numero_documento,
                'tipo_documento'  => $solicitud->tipo_documento,
                'password'        => $solicitud->password, // ya viene hasheado
                'activo'          => true,
            ]);

            $solicitud->update([
                'estado' => 'aprobado',
                'correo_bienvenida_enviado' => true,
            ]);

            // Enviar correo de bienvenida
            Mail::to($solicitud->correo)
                ->send(new BienvenidaEmpleadorMail($solicitud, $empresa));
        });

        return back()->with('exito', 'Solicitud aprobada. El usuario recibirá un correo de bienvenida y ya puede ingresar al sistema.');
    }

    // ── Rechazar ─────────────────────────────────────────────────────────
    public function rechazar(Request $request, SolicitudEmpleador $solicitud): RedirectResponse
    {
        $this->authorize('update', auth()->user()->empresa);

        $request->validate([
            'observaciones' => ['nullable', 'string', 'max:500'],
        ]);

        $empresa = $solicitud->empresa;

        $solicitud->update([
            'estado'        => 'rechazado',
            'observaciones' => $request->observaciones,
        ]);

        // Enviar correo de notificación de rechazo
        Mail::to($solicitud->correo)
            ->send(new SolicitudRechazadaMail($solicitud, $empresa));

        return back()->with('exito', 'Solicitud rechazada. Se ha enviado una notificación por correo.');
    }
}
