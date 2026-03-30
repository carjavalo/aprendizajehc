<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class CertificadoEditorController extends Controller
{
    /**
     * Roles permitidos para acceder al editor de certificados
     */
    protected $rolesPermitidos = ['Super Admin', 'Administrador', 'Operador'];

    /**
     * Verificar permisos de acceso
     */
    private function verificarAcceso(): void
    {
        $user = Auth::user();

        if (!$user || !in_array($user->role, $this->rolesPermitidos)) {
            abort(403, 'No tiene permisos para acceder a esta sección.');
        }
    }

    /**
     * Mostrar el editor de certificados
     */
    public function index(): View
    {
        $this->verificarAcceso();

        // Obtener usuarios para selección
        $usuarios = User::orderBy('name')->get();

        // Obtener cursos para selección
        $cursos = Curso::orderBy('titulo')->get();

        return view('admin.configuracion.editor-certificados.index', compact('usuarios', 'cursos'));
    }
}
