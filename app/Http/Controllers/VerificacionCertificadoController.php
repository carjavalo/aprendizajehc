<?php

namespace App\Http\Controllers;

use App\Models\CertificadoEmitido;
use Illuminate\Http\Request;

class VerificacionCertificadoController extends Controller
{
    /**
     * Mostrar formulario público de verificación.
     */
    public function formulario()
    {
        return view('verificacion.index');
    }

    /**
     * Verificar un certificado por su código.
     */
    public function verificar(string $codigo)
    {
        $certificado = CertificadoEmitido::with(['curso', 'estudiante', 'plantilla'])
            ->where('codigo_verificacion', $codigo)
            ->first();

        if (!$certificado) {
            return view('verificacion.resultado', [
                'encontrado' => false,
                'codigo' => $codigo,
            ]);
        }

        return view('verificacion.resultado', [
            'encontrado' => true,
            'certificado' => $certificado,
            'codigo' => $codigo,
        ]);
    }

    /**
     * Buscar certificado por código (POST desde formulario).
     */
    public function buscar(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string|max:64',
        ]);

        $codigo = strtoupper(trim($request->codigo));

        return redirect()->route('verificar.certificado', $codigo);
    }
}
