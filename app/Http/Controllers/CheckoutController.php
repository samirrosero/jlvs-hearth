<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    private array $planes = [
        'basico' => [
            'nombre'    => 'Básico',
            'precio'    => 150000,
            'medicos'   => '3',
            'pacientes' => '500',
        ],
        'profesional' => [
            'nombre'    => 'Profesional',
            'precio'    => 350000,
            'medicos'   => '15',
            'pacientes' => 'Ilimitados',
        ],
        'empresarial' => [
            'nombre'    => 'Empresarial',
            'precio'    => 750000,
            'medicos'   => 'Ilimitados',
            'pacientes' => 'Ilimitados',
        ],
    ];

    public function show(Request $request)
    {
        $plan = $request->query('plan', 'profesional');

        if (! array_key_exists($plan, $this->planes)) {
            $plan = 'profesional';
        }

        return view('checkout.index', [
            'plan'     => $plan,
            'planInfo' => $this->planes[$plan],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'plan'         => 'required|in:basico,profesional,empresarial',
            'nombre'       => 'required|string|max:255',
            'email'        => 'required|email|max:255',
            'telefono'     => 'nullable|string|max:20',
            'metodo_pago'  => 'required|in:pse,nequi,tarjeta',
        ]);

        session(['plan_seleccionado' => $request->plan]);

        return redirect()->route('onboarding.show');
    }
}
