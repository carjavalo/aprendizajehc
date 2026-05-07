<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use App\Models\Cargo;
use App\Models\Actividad;
use App\Models\VinculacionContrato;
use App\Models\Curso;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ReporteEstudiantesController extends Controller
{
    public function index()
    {
        $vinculaciones  = VinculacionContrato::orderBy('nombre')->get();
        $cargosRep      = Cargo::orderBy('nombre')->get();
        $actividadesRep = Actividad::orderBy('nombre')->get();
        return view('admin.consultas.reportes.index', compact('vinculaciones', 'cargosRep', 'actividadesRep'));
    }

    /**
     * Construye la query base del reporte aplicando filtros de rol y filtros opcionales.
     */
    private function buildQuery(Request $request)
    {
        $query = DB::table('curso_estudiantes')
            ->join('users', 'curso_estudiantes.estudiante_id', '=', 'users.id')
            ->join('cursos', 'curso_estudiantes.curso_id', '=', 'cursos.id')
            ->leftJoin('vinculacion_contrato', 'users.vinculacion_contrato_id', '=', 'vinculacion_contrato.id')
            ->leftJoin('servicios_areas', 'users.servicio_area_id', '=', 'servicios_areas.id')
            ->leftJoin('cargos', 'users.cargo_id', '=', 'cargos.id')
            ->leftJoin('actividades', 'users.actividad_id', '=', 'actividades.id')
            ->select([
                'curso_estudiantes.id as id',
                'curso_estudiantes.curso_id',
                'curso_estudiantes.estudiante_id',
                DB::raw("TRIM(CONCAT(users.name, ' ', COALESCE(users.apellido1, ''), ' ', COALESCE(users.apellido2, ''))) as nombre_completo"),
                DB::raw("CONCAT(COALESCE(users.tipo_documento,''), ' ', COALESCE(users.numero_documento,'')) as identificacion"),
                'vinculacion_contrato.nombre as vinculacion',
                'servicios_areas.nombre as area',
                'cargos.nombre as cargo_especialidad',
                'actividades.nombre as actividad',
                'users.phone as contacto',
                'users.email as correo',
                'cursos.titulo as curso',
                'cursos.nota_minima_aprobacion',
                'curso_estudiantes.fecha_inscripcion as fecha_inicio',
                'curso_estudiantes.ultima_actividad as fecha_fin',
                'curso_estudiantes.estado',
                'curso_estudiantes.progreso',
            ]);

        // Filtros por rol
        if (auth()->check() && auth()->user()->role === 'Consultor Agesoc') {
            $query->where('users.role', 'Estudiante')
                  ->where('vinculacion_contrato.nombre', 'Agesoc');
        } elseif (auth()->check() && auth()->user()->role === 'Consultor Asstracud') {
            $query->where('users.role', 'Estudiante')
                  ->where('vinculacion_contrato.nombre', 'Asstracud');
        }

        // Filtros opcionales del usuario
        if ($request->filled('vinculacion_id')) {
            $query->where('users.vinculacion_contrato_id', $request->vinculacion_id);
        }
        if ($request->filled('cargo_id_filter')) {
            $query->where('users.cargo_id', $request->cargo_id_filter);
        }
        if ($request->filled('actividad_id_filter')) {
            $query->where('users.actividad_id', $request->actividad_id_filter);
        }

        return $query;
    }

    public function getData(Request $request)
    {
        $query = $this->buildQuery($request);

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
            ->filterColumn('cargo_especialidad', function($query, $keyword) {
                $query->where('cargos.nombre', 'like', "%{$keyword}%");
            })
            ->filterColumn('actividad', function($query, $keyword) {
                $query->where('actividades.nombre', 'like', "%{$keyword}%");
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
                if ($row->estado === 'inactivo') {
                    return '<span class="badge badge-secondary">Inactivo</span>';
                }
                if ($row->estado === 'abandonado') {
                    return '<span class="badge badge-dark">Abandonado</span>';
                }

                $tieneEntregas = DB::table('curso_actividad_entrega')
                    ->where('curso_id', $row->curso_id)
                    ->where('user_id', $row->estudiante_id)
                    ->whereNotNull('calificacion')
                    ->exists();

                if (!$tieneEntregas) {
                    return '<span class="badge badge-info">En Curso</span>';
                }

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
                            return '<span class="badge badge-success">' . number_format($notaFinal, 2) . '/5.0 - Aprobó</span>';
                        } else {
                            return '<span class="badge badge-danger">' . number_format($notaFinal, 2) . '/5.0 - Reprobó</span>';
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

    public function export(Request $request)
    {
        $rows = $this->buildQuery($request)->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Reporte Estudiantes');

        // Cabeceras
        $cols = [
            'A' => 'Nombre Completo',
            'B' => 'Identificación',
            'C' => 'Tipo Vinculación',
            'D' => 'Área',
            'E' => 'Cargo / Especialidad',
            'F' => 'Actividad',
            'G' => 'Contacto',
            'H' => 'Correo',
            'I' => 'Curso',
            'J' => 'Fecha Inicio',
            'K' => 'Fecha Fin',
            'L' => 'Estado',
            'M' => 'Progreso (%)',
        ];

        $colIndex = 1;
        foreach ($cols as $header) {
            $sheet->setCellValueByColumnAndRow($colIndex, 1, $header);
            $colIndex++;
        }

        // Estilo de cabecera
        $lastCol = 'M';
        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2c4370']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(22);

        // Datos
        $rowIndex = 2;
        foreach ($rows as $row) {
            $sheet->setCellValueByColumnAndRow(1,  $rowIndex, $row->nombre_completo ?? '');
            $sheet->setCellValueByColumnAndRow(2,  $rowIndex, $row->identificacion ?? '');
            $sheet->setCellValueByColumnAndRow(3,  $rowIndex, $row->vinculacion ?? '');
            $sheet->setCellValueByColumnAndRow(4,  $rowIndex, $row->area ?? '');
            $sheet->setCellValueByColumnAndRow(5,  $rowIndex, $row->cargo_especialidad ?? '');
            $sheet->setCellValueByColumnAndRow(6,  $rowIndex, $row->actividad ?? '');
            $sheet->setCellValueByColumnAndRow(7,  $rowIndex, $row->contacto ?? '');
            $sheet->setCellValueByColumnAndRow(8,  $rowIndex, $row->correo ?? '');
            $sheet->setCellValueByColumnAndRow(9,  $rowIndex, $row->curso ?? '');
            $sheet->setCellValueByColumnAndRow(10, $rowIndex, $row->fecha_inicio ? date('Y-m-d H:i', strtotime($row->fecha_inicio)) : '');
            $sheet->setCellValueByColumnAndRow(11, $rowIndex, $row->fecha_fin ? date('Y-m-d H:i', strtotime($row->fecha_fin)) : '');
            $sheet->setCellValueByColumnAndRow(12, $rowIndex, ucfirst($row->estado ?? ''));
            $sheet->setCellValueByColumnAndRow(13, $rowIndex, $row->progreso ?? 0);

            // Alternar color de filas
            if ($rowIndex % 2 === 0) {
                $sheet->getStyle("A{$rowIndex}:{$lastCol}{$rowIndex}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'e8edf5']],
                ]);
            }
            $rowIndex++;
        }

        // Bordes en el área de datos
        if ($rowIndex > 2) {
            $sheet->getStyle("A1:{$lastCol}" . ($rowIndex - 1))->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
            ]);
        }

        // Auto-ajustar columnas
        foreach (range(1, count($cols)) as $i) {
            $sheet->getColumnDimensionByColumn($i)->setAutoSize(true);
        }

        $filename = 'reporte_estudiantes_' . date('Ymd_His') . '.xlsx';

        $writer = new Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
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
            'estado'   => 'required',
            'progreso' => 'required|integer|min:0|max:100',
        ]);

        DB::table('curso_estudiantes')->where('id', $id)->update([
            'estado'   => $request->estado,
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
