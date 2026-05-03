<?php

namespace App\Http\Controllers\Gestor;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Pago;
use App\Models\Paciente;
use App\Models\PrecioServicio;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GestorRecepcionController extends Controller
{
    /**
     * Vista principal de recepción - buscar paciente por cédula
     */
    public function index(): View
    {
        return view('gestor.recepcion.index');
    }

    /**
     * Buscar citas del paciente para hoy
     */
    public function buscar(Request $request)
    {
        $request->validate([
            'identificacion' => 'required|string'
        ]);

        $empresaId = auth()->user()->empresa_id;
        $hoy = Carbon::today();

        // Buscar paciente
        $paciente = Paciente::where('identificacion', $request->identificacion)
            ->where('empresa_id', $empresaId)
            ->with('portafolio')
            ->first();

        if (!$paciente) {
            return back()->with('error', 'Paciente no encontrado con esa identificación.');
        }

        // Buscar citas del paciente para hoy (pendientes o confirmadas)
        $citas = Cita::where('paciente_id', $paciente->id)
            ->whereDate('fecha', $hoy)
            ->whereIn('estado_id', [1, 2]) // pendiente, confirmada
            ->where('activo', true)
            ->with(['medico.usuario', 'servicio', 'modalidad', 'estado'])
            ->get();

        // Verificar pagos existentes
        foreach ($citas as $cita) {
            $cita->pago = Pago::where('cita_id', $cita->id)->first();
            
            // Calcular precio según portafolio
            $precio = PrecioServicio::where('servicio_id', $cita->servicio_id)
                ->where('portafolio_id', $paciente->portafolio_id)
                ->where('empresa_id', $empresaId)
                ->first();
            
            $cita->precio_sugerido = $precio?->precio ?? $cita->servicio?->nombre ?? 'No disponible';
        }

        return view('gestor.recepcion.index', compact('paciente', 'citas'));
    }

    /**
     * Mostrar formulario de pago para una cita
     */
    public function formularioPago(Cita $cita): View
    {
        abort_if($cita->empresa_id !== auth()->user()->empresa_id, 403);
        
        $paciente = $cita->paciente()->with('portafolio')->first();
        
        // Buscar precio según portafolio
        $precio = PrecioServicio::where('servicio_id', $cita->servicio_id)
            ->where('portafolio_id', $paciente->portafolio_id)
            ->where('empresa_id', auth()->user()->empresa_id)
            ->first();

        $montoSugerido = $precio?->precio ?? 0;

        return view('gestor.recepcion.pago', compact('cita', 'paciente', 'montoSugerido'));
    }

    /**
     * Registrar pago
     */
    public function registrarPago(Request $request, Cita $cita): RedirectResponse
    {
        abort_if($cita->empresa_id !== auth()->user()->empresa_id, 403);

        $request->validate([
            'monto' => 'required|numeric|min:0',
            'metodo_pago' => 'required|in:efectivo,tarjeta,transferencia,prepagada,seguro,empresarial',
            'referencia' => 'nullable|string|max:100',
            'observaciones' => 'nullable|string',
        ]);

        $paciente = $cita->paciente;

        Pago::create([
            'empresa_id' => auth()->user()->empresa_id,
            'cita_id' => $cita->id,
            'paciente_id' => $paciente->id,
            'monto' => $request->monto,
            'metodo_pago' => $request->metodo_pago,
            'estado' => 'pagado',
            'tipo_pago' => $cita->modalidad?->nombre === 'Telemedicina' ? 'telemedicina' : 'presencial',
            'referencia' => $request->referencia,
            'fecha_pago' => now(),
            'observaciones' => $request->observaciones,
        ]);

        return redirect()->route('gestor.recepcion.index')
            ->with('exito', 'Pago registrado correctamente. El paciente puede pasar a consulta.');
    }

    /**
     * Confirmar llegada del paciente (sin pago - para casos de prepagada/seguro)
     */
    public function confirmarLlegada(Cita $cita): RedirectResponse
    {
        abort_if($cita->empresa_id !== auth()->user()->empresa_id, 403);

        // Actualizar estado de la cita a "Confirmada" si está pendiente
        if ($cita->estado?->nombre === 'Pendiente') {
            $estadoConfirmada = \App\Models\EstadoCita::where('nombre', 'Confirmada')->first();
            if ($estadoConfirmada) {
                $cita->update(['estado_id' => $estadoConfirmada->id]);
            }
        }

        return redirect()->route('gestor.recepcion.index')
            ->with('exito', 'Llegada del paciente confirmada. Cita actualizada.');
    }
}
