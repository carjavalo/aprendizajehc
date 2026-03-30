<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\User;
use App\Models\PlantillaCertificado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class CertificadoPlantillaController extends Controller
{
    protected $rolesPermitidos = ['Super Admin', 'Administrador', 'Operador'];

    private function verificarAcceso(): void
    {
        $user = Auth::user();

        if (!$user || !in_array($user->role, $this->rolesPermitidos)) {
            abort(403, 'No tiene permisos para acceder a esta sección.');
        }
    }

    public function index(): View
    {
        $this->verificarAcceso();
        $docentes = User::where('role', 'Docente')->orderBy('name')->get();
        $usuarios = $docentes; // Mantener la variable para no romper la vista si se usa en otro lado
        $cursos = Curso::orderBy('titulo')->get();
        $plantillas = PlantillaCertificado::latest()->get();

        return view('admin.configuracion.editor-certificados.index', compact('usuarios', 'cursos', 'plantillas'));
    }

    public function store(Request $request)
    {
        $this->verificarAcceso();

        $request->validate([
            'nombre' => 'required|string|max:255',
            'elementos_json' => 'nullable|string',
            'html_content' => 'nullable|string',
            'fondo_base64' => 'nullable|string',
            'curso_id' => 'nullable|exists:cursos,id'
        ]);

        $plantilla = new PlantillaCertificado();
        $plantilla->nombre = $request->nombre;
        
        $elementosStr = $request->elementos_json ?? '[]';
        $plantilla->elementos_json = json_decode($elementosStr, true);
        $plantilla->html_content = $request->html_content;

        if ($request->filled('fondo_base64')) {
            // we will just store base64 in the elements_json or fondo_path for now
            // simpler than uploading file just for the template background
            $data = $plantilla->elementos_json ?? [];
            $data['fondo_base64'] = $request->fondo_base64;
            $plantilla->elementos_json = $data;
        }

        $plantilla->save();

        // Generar archivo de fondo en disco para mejor rendimiento y compatibilidad con cPanel
        // Esto invoca el accessor fondo_url que extrae el base64 y lo guarda como archivo
        try {
            $plantilla->fondo_url;
        } catch (\Throwable $e) {
            \Log::warning('No se pudo generar archivo de fondo para plantilla #' . $plantilla->id . ': ' . $e->getMessage());
        }

        // Asignar automáticamente la plantilla al curso seleccionado
        if ($request->filled('curso_id')) {
            Curso::where('id', $request->curso_id)->update(['plantilla_certificado_id' => $plantilla->id]);
        }

        return redirect()->route('configuracion.editor-certificados.index')->with('success', 'Plantilla guardada y asignada al curso correctamente.');
    }

    public function destroy(PlantillaCertificado $plantilla)
    {
        $this->verificarAcceso();

        $cursosUsando = $plantilla->cursos()->count();
        if ($cursosUsando > 0) {
            if (request()->expectsJson()) {
                return response()->json(['error' => 'No se puede eliminar la plantilla porque está asignada a ' . $cursosUsando . ' cursos.'], 422);
            }
            return redirect()->back()->with('error', 'No se puede eliminar la plantilla porque está asignada a ' . $cursosUsando . ' cursos.');
        }

        $plantilla->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Plantilla eliminada correctamente.']);
        }
        return redirect()->route('configuracion.editor-certificados.index')->with('success', 'Plantilla eliminada correctamente.');
    }

    public function showJson(PlantillaCertificado $plantilla)
    {
        $this->verificarAcceso();
        return response()->json($plantilla);
    }

    /**
     * Devolver datos de plantillas para DataTable (JSON)
     */
    public function getData()
    {
        $this->verificarAcceso();

        $plantillas = PlantillaCertificado::latest()->get()->map(function ($p) {
            $elementos = $p->elementos_json ?? [];
            return [
                'id' => $p->id,
                'nombre' => $p->nombre,
                'firma_nombre' => $elementos['firma_nombre'] ?? '-',
                'firma_cargo' => $elementos['firma_cargo'] ?? '-',
                'cursos_count' => $p->cursos()->count(),
                'created_at' => $p->created_at ? $p->created_at->format('d/m/Y H:i') : '-',
            ];
        });

        return response()->json(['data' => $plantillas]);
    }

    /**
     * Actualizar una plantilla (nombre, firma, cargo)
     */
    public function update(Request $request, PlantillaCertificado $plantilla)
    {
        $this->verificarAcceso();

        $request->validate([
            'nombre' => 'required|string|max:255',
            'firma_nombre' => 'nullable|string|max:255',
            'firma_cargo' => 'nullable|string|max:255',
        ]);

        $plantilla->nombre = $request->nombre;

        // Actualizar firma y cargo dentro de elementos_json
        $elementos = $plantilla->elementos_json ?? [];
        $elementos['firma_nombre'] = $request->firma_nombre ?? '';
        $elementos['firma_cargo'] = $request->firma_cargo ?? '';
        $plantilla->elementos_json = $elementos;

        // Actualizar también en el html_content si existe
        if ($plantilla->html_content) {
            $html = $plantilla->html_content;
            // Actualizar el texto de firma en el HTML del certificado
            $html = preg_replace(
                '/(id="certFirmaNombre"[^>]*>)[^<]*(</','$1' . strtoupper($request->firma_nombre ?? '') . '$2',
                $html
            );
            $html = preg_replace(
                '/(id="certFirmaCargo"[^>]*>)[^<]*(</','$1' . strtoupper($request->firma_cargo ?? '') . '$2',
                $html
            );
            $plantilla->html_content = $html;
        }

        $plantilla->save();

        // Regenerar archivo de fondo si hubo cambios en elementos_json
        try {
            // Eliminar archivo previo para forzar regeneración
            $relativePath = 'certificados/fondos/plantilla_' . $plantilla->id . '.png';
            if (\Storage::disk('public')->exists($relativePath)) {
                \Storage::disk('public')->delete($relativePath);
            }
            $plantilla->fondo_url;
        } catch (\Throwable $e) {
            \Log::warning('No se pudo regenerar fondo para plantilla #' . $plantilla->id);
        }

        return response()->json(['success' => true, 'message' => 'Plantilla actualizada correctamente.']);
    }

    public function getCursosPorDocente(User $docente)
    {
        $this->verificarAcceso();
        
        // Obtener cursos donde es instructor o está asignado como docente
        $cursosIds = \App\Models\CursoAsignacion::where('docente_id', $docente->id)->pluck('curso_id')->toArray();
        
        $cursos = Curso::where('instructor_id', $docente->id)
            ->orWhereIn('id', $cursosIds)
            ->select('id', 'titulo', 'duracion_horas', 'fecha_inicio', 'fecha_fin')
            ->orderBy('titulo')
            ->get();

        return response()->json($cursos);
    }

    /**
     * Obtener estudiantes inscritos en un curso con estado de aprobación
     */
    public function getEstudiantesPorCurso(Curso $curso)
    {
        $this->verificarAcceso();

        // Estudiantes inscritos activos en el curso
        $estudiantes = $curso->estudiantes()
            ->wherePivot('estado', 'activo')
            ->orderBy('name')
            ->get()
            ->map(function ($est) use ($curso) {
                $resumen = $curso->getResumenCalificacionesEstudiante($est->id);
                return [
                    'id' => $est->id,
                    'name' => $est->name,
                    'apellido1' => $est->apellido1 ?? '',
                    'apellido2' => $est->apellido2 ?? '',
                    'numero_documento' => $est->numero_documento ?? '',
                    'email' => $est->email,
                    'nota_final' => round($resumen['nota_final'], 1),
                    'aprobado' => $resumen['aprobado'],
                    'fecha_inscripcion' => $est->pivot->fecha_inscripcion ? \Carbon\Carbon::parse($est->pivot->fecha_inscripcion)->format('Y-m-d') : null,
                    'fecha_completado' => $est->pivot->ultima_actividad ? \Carbon\Carbon::parse($est->pivot->ultima_actividad)->format('Y-m-d') : null,
                ];
            });

        return response()->json($estudiantes);
    }
}









