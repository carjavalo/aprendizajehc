<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use App\Models\Curso;

class ReporteEstudiantesController extends Controller
{
    public function index()
    {
        return view('admin.consultas.reportes.index');
    }

    public function getData(Request $request)
    {
        $query = DB::table('curso_estudiantes')
            ->join('users', 'curso_estudiantes.estudiante_id', '=', 'users.id')
            ->join('cursos', 'curso_estudiantes.curso_id', '=', 'cursos.id')
            ->leftJoin('vinculacion_contrato', 'users.vinculacion_contrato_id', '=', 'vinculacion_contrato.id')
            ->leftJoin('servicios_areas', 'users.servicio_area_id', '=', 'servicios_areas.id')
            ->select([
                'curso_estudiantes.id as id',
                'curso_estudiantes.curso_id',
                'curso_estudiantes.estudiante_id',
                DB::raw("TRIM(CONCAT(users.name, ' ', COALESCE(users.apellido1, ''), ' ', COALESCE(users.apellido2, ''))) as nombre_completo"),
                DB::raw("CONCAT(COALESCE(users.tipo_documento,''), ' ', COALESCE(users.numero_documento,'')) as identificacion"),
                'vinculacion_contrato.nombre as vinculacion',
                'servicios_areas.nombre as area',
                'users.phone as contacto',
                'users.email as correo',
                'cursos.titulo as curso',
                'cursos.nota_minima_aprobacion',
                'curso_estudiantes.fecha_inscripcion as fecha_inicio',
                'curso_estudiantes.ultima_actividad as fecha_fin',
                'curso_estudiantes.estado',
                'curso_estudiantes.progreso'
            ]);

        // Filtrar si el usuario es "Consultor Agesoc"
        if (auth()->check() && auth()->user()->role === 'Consultor Agesoc') {
            $query->where('users.role', 'Estudiante')
                  ->where('vinculacion_contrato.nombre', 'Agesoc');
        } elseif (auth()->check() && auth()->user()->role === 'Consultor Asstracud') {
            $query->where('users.role', 'Estudiante')
                  ->where('vinculacion_contrato.nombre', 'Asstracud');
        }

        // Precargar cursos con sus materiales y actividades para calcular notas reales
        $cursoCache = [];

        return DataTables::of($query)
            ->filterColumn('nombre_completo', function($query, $keyword) {
                $query->whereRaw("CONCAT(users.name, ' ', COALESCE(users.apellido1, ''), ' ', COALESCE(users.apellido2, '')) like ?", ["%{$keyword}%"]);
            })
            ->filterColumn('identificacion', function($query, $keyword) {
                $query->whereRaw("CONCAT(COALESCE(users.tipo_documento,''), ' ', COALESCE(users.numero_documento,'')) like ?", ["%{$keyword}%"]);
            })
            ->filterColumn('vinculacion', function($query, $keyword) {
                $query->where('vinculacion_contrato.nombre', 'like', "%{$keyword}%");
            })
            ->filterColumn('area', function($query, $keyword) {
                $query->where('servicios_areas.nombre', 'like', "%{$keyword}%");
            })
            ->filterColumn('contacto', function($query, $keyword) {
                $query->where('users.phone', 'like', "%{$keyword}%");
            })
            ->filterColumn('correo', function($query, $keyword) {
                $query->where('users.email', 'like', "%{$keyword}%");
            })
            ->filterColumn('curso', function($query, $keyword) {
                $query->where('cursos.titulo', 'like', "%{$keyword}%");
            })
            ->filterColumn('fecha_inicio', function($query, $keyword) {
                $query->whereRaw("DATE_FORMAT(curso_estudiantes.fecha_inscripcion, '%Y-%m-%d') like ?", ["%{$keyword}%"]);
            })
            ->filterColumn('fecha_fin', function($query, $keyword) {
                $query->whereRaw("DATE_FORMAT(curso_estudiantes.ultima_actividad, '%Y-%m-%d') like ?", ["%{$keyword}%"]);
            })
            ->addColumn('estado_badge', function($row) use (&$cursoCache) {
                // Si el estudiante está inactivo o abandonó
                if ($row->estado === 'inactivo') {
                    return '<span class="badge badge-secondary">Inactivo</span>';
                }
                if ($row->estado === 'abandonado') {
                    return '<span class="badge badge-dark">Abandonado</span>';
                }

                // Verificar si tiene entregas de actividades en este curso
                $tieneEntregas = DB::table('curso_actividad_entrega')
                    ->where('curso_id', $row->curso_id)
                    ->where('user_id', $row->estudiante_id)
                    ->whereNotNull('calificacion')
                    ->exists();

                if (!$tieneEntregas) {
                    return '<span class="badge badge-info">En Curso</span>';
                }

                // Calcular nota real usando el modelo Curso
                try {
                    if (!isset($cursoCache[$row->curso_id])) {
                        $cursoCache[$row->curso_id] = Curso::with(['materiales.actividades'])->find($row->curso_id);
                    }
                    $cursoModel = $cursoCache[$row->curso_id];

                    if ($cursoModel) {
                        $notaFinal = $cursoModel->calcularNotaFinalEstudiante($row->estudiante_id);
                        $notaMinima = $cursoModel->nota_minima_aprobacion ?? 3.0;
                        $aprobado = $notaFinal >= $notaMinima;

                        if ($aprobado) {
                            return '<span class="badge badge-success">' . number_format($notaFinal, 2) . '/5.0 - Aprob\u00f3</span>';
                        } else {
                            return '<span class="badge badge-danger">' . number_format($notaFinal, 2) . '/5.0 - Reprob\u00f3</span>';
                        }
                    }
                } catch (\Exception $e) {
                    // Fallback en caso de error
                }

                return '<span class="badge badge-info">En Curso</span>';
            })
            ->addColumn('action', function($row){
                $btn = '<div class="btn-group">';
                if (Gate::allows('reportes.view')) {
                    $btn .= '<button type="button" class="btn btn-sm btn-info viewRecord" data-id="'.$row->id.'" title="Ver"><i class="fas fa-eye text-white"></i></button>';
                }
                if (Gate::allows('reportes.edit')) {
                    $btn .= '<button type="button" class="btn btn-sm btn-primary editRecord" data-id="'.$row->id.'" title="Editar"><i class="fas fa-edit"></i></button>';
                }
                if (Gate::allows('reportes.delete')) {
                    $btn .= '<button type="button" class="btn btn-sm btn-danger deleteRecord" data-id="'.$row->id.'" title="Eliminar"><i class="fas fa-trash"></i></button>';
                }
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['estado_badge', 'action'])
            ->make(true);
    }

    public function show($id)
    {
        $record = DB::table('curso_estudiantes')
            ->join('users', 'curso_estudiantes.estudiante_id', '=', 'users.id')
            ->join('cursos', 'curso_estudiantes.curso_id', '=', 'cursos.id')
            ->select('curso_estudiantes.*', 'users.name as user_name', 'cursos.titulo as curso_titulo')
            ->where('curso_estudiantes.id', $id)
            ->first();
            
        if (!$record) return response()->json(['error' => 'No encontrado'], 404);

        // Calcular nota real
        $curso = Curso::with(['materiales.actividades'])->find($record->curso_id);
        $notaFinal = 0;
        $aprobado = false;
        if ($curso) {
            $notaFinal = $curso->calcularNotaFinalEstudiante($record->estudiante_id);
            $aprobado = $curso->estudianteAprobo($record->estudiante_id);
        }

        $data = (array) $record;
        $data['nota_final'] = number_format($notaFinal, 2);
        $data['aprobado'] = $aprobado;
        $data['nota_minima'] = $curso->nota_minima_aprobacion ?? 3.0;

        return response()->json($data);
    }

    public function edit($id)
    {
        $record = DB::table('curso_estudiantes')
            ->join('users', 'curso_estudiantes.estudiante_id', '=', 'users.id')
            ->join('cursos', 'curso_estudiantes.curso_id', '=', 'cursos.id')
            ->select('curso_estudiantes.*', 'users.name as user_name', 'cursos.titulo as curso_titulo')
            ->where('curso_estudiantes.id', $id)
            ->first();
            
        if (!$record) return response()->json(['error' => 'No encontrado'], 404);
        return response()->json($record);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required',
            'progreso' => 'required|integer|min:0|max:100',
        ]);

        DB::table('curso_estudiantes')->where('id', $id)->update([
            'estado' => $request->estado,
            'progreso' => $request->progreso,
        ]);

        return response()->json(['success' => 'Registro actualizado correctamente.']);
    }

    public function destroy($id)
    {
        DB::table('curso_estudiantes')->where('id', $id)->delete();
        return response()->json(['success' => 'Registro eliminado correctamente.']);
    }
}
