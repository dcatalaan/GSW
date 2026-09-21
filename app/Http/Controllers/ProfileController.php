<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Ver perfil y datos de facturación
     */
    public function show(Request $request)
    {
        return view('profile.show', ['user' => $request->user()]);
    }

    /**
     * Actualizar datos de facturación
     */
    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'billing_name' => 'nullable|string|max:150',
            'doc_type' => 'nullable|in:DUI,NIT',
            'doc_number' => 'nullable|string|max:20',
            'nrc' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'billing_address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
        ]);

        $request->user()->update($request->only([
            'name', 'billing_name', 'doc_type', 'doc_number',
            'nrc', 'phone', 'billing_address', 'city',
        ]));

        return back()->with('success', 'Perfil actualizado correctamente.');
    }
}
