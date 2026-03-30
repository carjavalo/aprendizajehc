<?php

namespace App\Services;

use App\Models\Curso;
use App\Models\CursoMaterial;
use App\Models\CursoActividad;
use Illuminate\Support\Facades\DB;

class GradingService
{
    /**
     * Nota máxima del sistema
     */
    const NOTA_MAXIMA = 5.0;

    /**
     * Porcentaje máximo del curso
     */
    const PORCENTAJE_MAXIMO = 100.0;

    /**
     * Validar configuración de nota mínima de aprobación del curso
     */
    public function validarNotaMinimaAprobacionCurso(float $nota): array
    {
        $errores = [];
        
        if ($nota < 0) {
            $errores[] = 'La nota mínima de aprobación no puede ser negativa.';
        }
        
        if ($nota > self::NOTA_MAXIMA) {
            $errores[] = 'La nota mínima de aprobación no puede ser mayor a ' . self::NOTA_MAXIMA . '.';
        }
        
        return $errores;
    }

    /**
     * Validar porcentaje de un material en el curso
     */
    public function validarPorcentajeMaterial(Curso $curso, float $porcentaje, ?int $materialIdExcluir = null): array
    {
        $errores = [];
        
        if ($porcentaje < 0) {
            $errores[] = 'El porcentaje del material no puede ser negativo.';
        }
        
        if ($porcentaje > self::PORCENTAJE_MAXIMO) {
            $errores[] = 'El porcentaje del material no puede ser mayor a ' . self::PORCENTAJE_MAXIMO . '%.';
        }
        
        // Calcular porcentaje total actual
        $porcentajeActual = $curso->materiales()
            ->when($materialIdExcluir, function($query) use ($materialIdExcluir) {
                return $query->where('id', '!=', $materialIdExcluir);
            })
            ->sum('porcentaje_curso');
        
        $porcentajeTotal = $porcentajeActual + $porcentaje;
        
        if ($porcentajeTotal > self::PORCENTAJE_MAXIMO) {
            $disponible = self::PORCENTAJE_MAXIMO - $porcentajeActual;
            $errores[] = "El porcentaje total de materiales excede el 100%. Porcentaje disponible: {$disponible}%.";
        }
        
        return $errores;
    }

    /**
     * Validar nota mínima de aprobación de un material
     */
    public function validarNotaMinimaAprobacionMaterial(float $nota): array
    {
        $errores = [];
        
        if ($nota < 0) {
            $errores[] = 'La nota mínima de aprobación del material no puede ser negativa.';
        }
        
        if ($nota > self::NOTA_MAXIMA) {
            $errores[] = 'La nota mínima de aprobación del material no puede ser mayor a ' . self::NOTA_MAXIMA . '.';
        }
        
        return $errores;
    }

    /**
     * Validar porcentaje de una actividad
     * El porcentaje de la actividad es relativo al material (0-100%), no al curso
     * La suma de porcentajes de actividades de un material no puede superar 100%
     */
    public function validarPorcentajeActividad(
        Curso $curso, 
        ?CursoMaterial $material, 
        float $porcentaje, 
        ?int $actividadIdExcluir = null
    ): array {
        $errores = [];
        
        if ($porcentaje < 0) {
            $errores[] = 'El porcentaje de la actividad no puede ser negativo.';
        }
        
        if ($porcentaje > self::PORCENTAJE_MAXIMO) {
            $errores[] = 'El porcentaje de la actividad no puede ser mayor a ' . self::PORCENTAJE_MAXIMO . '%.';
        }
        
        // Si la actividad está vinculada a un material, validar contra 100% del material
        if ($material) {
            // El porcentaje de actividades es relativo al material (0-100%)
            $porcentajeActual = $material->actividades()
                ->when($actividadIdExcluir, function($query) use ($actividadIdExcluir) {
                    return $query->where('id', '!=', $actividadIdExcluir);
                })
                ->sum('porcentaje_curso'); // Este campo almacena el % relativo al material
            
            $porcentajeTotal = $porcentajeActual + $porcentaje;
            
            // La suma no puede exceder 100% del material
            if ($porcentajeTotal > self::PORCENTAJE_MAXIMO) {
                $disponible = self::PORCENTAJE_MAXIMO - $porcentajeActual;
                $errores[] = "El porcentaje total de actividades del material excede el 100%. Porcentaje disponible: {$disponible}%.";
            }
        } else {
            // Si no está vinculada a un material, la actividad no es válida
            $errores[] = "La actividad debe estar vinculada a un material.";
        }
        
        return $errores;
    }

    /**
     * Validar puntos de preguntas de un quiz/evaluación
     */
    public function validarPuntosPreguntas(array $preguntas): array
    {
        $errores = [];
        $totalPuntos = 0;
        
        foreach ($preguntas as $index => $pregunta) {
            $puntos = floatval($pregunta['points'] ?? 0);
            
            if ($puntos < 0) {
                $errores[] = "La pregunta " . ($index + 1) . " tiene puntos negativos.";
            }
            
            if ($puntos > self::NOTA_MAXIMA) {
                $errores[] = "La pregunta " . ($index + 1) . " tiene más de " . self::NOTA_MAXIMA . " puntos.";
            }
            
            $totalPuntos += $puntos;
        }
        
        if ($totalPuntos > self::NOTA_MAXIMA) {
            $errores[] = "La suma total de puntos de las preguntas ({$totalPuntos}) excede la nota máxima de " . self::NOTA_MAXIMA . ".";
        }
        
        return $errores;
    }

    /**
     * Validar calificación de una tarea
     */
    public function validarCalificacionTarea(float $calificacion): array
    {
        $errores = [];
        
        if ($calificacion < 0) {
            $errores[] = 'La calificación no puede ser negativa.';
        }
        
        if ($calificacion > self::NOTA_MAXIMA) {
            $errores[] = 'La calificación no puede ser mayor a ' . self::NOTA_MAXIMA . '.';
        }
        
        return $errores;
    }

    /**
     * Obtener resumen de porcentajes del curso
     */
    public function getResumenPorcentajesCurso(Curso $curso): array
    {
        $porcentajeMateriales = $curso->materiales()->sum('porcentaje_curso');
        $porcentajeActividadesSinMaterial = $curso->actividades()
            ->whereNull('material_id')
            ->sum('porcentaje_curso');
        
        $porcentajeTotal = $porcentajeMateriales + $porcentajeActividadesSinMaterial;
        
        return [
            'porcentaje_materiales' => $porcentajeMateriales,
            'porcentaje_actividades_sin_material' => $porcentajeActividadesSinMaterial,
            'porcentaje_total_asignado' => $porcentajeTotal,
            'porcentaje_disponible' => self::PORCENTAJE_MAXIMO - $porcentajeTotal,
            'es_valido' => $porcentajeTotal <= self::PORCENTAJE_MAXIMO,
        ];
    }

    /**
     * Obtener resumen de porcentajes de un material
     * El porcentaje de actividades es relativo al material (0-100%)
     */
    public function getResumenPorcentajesMaterial(CursoMaterial $material): array
    {
        // El porcentaje de actividades es sobre 100% del material
        $porcentajeActividades = $material->actividades()->sum('porcentaje_curso');
        
        return [
            'porcentaje_material_en_curso' => $material->porcentaje_curso, // % del material en el curso
            'porcentaje_actividades_asignado' => $porcentajeActividades, // % de actividades sobre el material (0-100)
            'porcentaje_disponible' => self::PORCENTAJE_MAXIMO - $porcentajeActividades, // % disponible sobre el material
            'es_valido' => $porcentajeActividades <= self::PORCENTAJE_MAXIMO,
        ];
    }

    /**
     * Calcular la nota final de un estudiante en un curso
     * 
     * La nota se calcula así:
     * 1. Cada material tiene un porcentaje del curso (0-100%)
     * 2. Cada actividad tiene un porcentaje del material (0-100%)
     * 3. La nota de la actividad (0-5.0) se pondera por su porcentaje del material
     * 4. La nota del material es la suma ponderada de sus actividades
     * 5. La nota del curso es la suma ponderada de los materiales
     * 
     * Ejemplo:
     * - Material A: 40% del curso
     *   - Actividad 1: 50% del material, nota 4.0 → contribuye 4.0 * 0.50 = 2.0 al material
     *   - Actividad 2: 50% del material, nota 3.5 → contribuye 3.5 * 0.50 = 1.75 al material
     *   - Nota del material A: 2.0 + 1.75 = 3.75
     *   - Contribución al curso: 3.75 * 0.40 = 1.5
     * 
     * @param Curso $curso
     * @param int $userId
     * @return array
     */
    public function calcularNotaFinalEstudiante(Curso $curso, int $userId): array
    {
        $detalles = [];
        $notaFinalCurso = 0;
        $porcentajeTotalEvaluado = 0;
        
        // Obtener todos los materiales del curso
        $materiales = $curso->materiales()->with('actividades')->get();
        
        foreach ($materiales as $material) {
            $notaMaterial = 0;
            $porcentajeActividadesEvaluadas = 0;
            $actividadesDetalle = [];
            
            foreach ($material->actividades as $actividad) {
                // Obtener la nota del estudiante en esta actividad (0-5.0)
                $notaActividad = $actividad->calcularNotaEstudiante($userId);
                
                // El porcentaje de la actividad es relativo al material (0-100%)
                $porcentajeActividad = floatval($actividad->porcentaje_curso) / 100;
                
                // Contribución de esta actividad a la nota del material
                $contribucionActividad = $notaActividad * $porcentajeActividad;
                $notaMaterial += $contribucionActividad;
                $porcentajeActividadesEvaluadas += floatval($actividad->porcentaje_curso);
                
                $actividadesDetalle[] = [
                    'actividad_id' => $actividad->id,
                    'titulo' => $actividad->titulo,
                    'tipo' => $actividad->tipo,
                    'nota' => $notaActividad,
                    'porcentaje_material' => floatval($actividad->porcentaje_curso),
                    'contribucion_material' => round($contribucionActividad, 2),
                    'nota_minima_aprobacion' => floatval($actividad->nota_minima_aprobacion),
                    'aprobado' => $notaActividad >= floatval($actividad->nota_minima_aprobacion),
                ];
            }
            
            // El porcentaje del material es relativo al curso (0-100%)
            $porcentajeMaterial = floatval($material->porcentaje_curso) / 100;
            
            // Contribución de este material a la nota del curso
            $contribucionMaterial = $notaMaterial * $porcentajeMaterial;
            $notaFinalCurso += $contribucionMaterial;
            $porcentajeTotalEvaluado += floatval($material->porcentaje_curso);
            
            $detalles[] = [
                'material_id' => $material->id,
                'titulo' => $material->titulo,
                'porcentaje_curso' => floatval($material->porcentaje_curso),
                'nota_material' => round($notaMaterial, 2),
                'contribucion_curso' => round($contribucionMaterial, 2),
                'nota_minima_aprobacion' => floatval($material->nota_minima_aprobacion),
                'aprobado' => $notaMaterial >= floatval($material->nota_minima_aprobacion),
                'porcentaje_actividades_evaluadas' => $porcentajeActividadesEvaluadas,
                'actividades' => $actividadesDetalle,
            ];
        }
        
        // Verificar si el estudiante aprobó el curso
        $notaMinimaAprobacionCurso = floatval($curso->nota_minima_aprobacion ?? 3.0);
        $aprobadoCurso = $notaFinalCurso >= $notaMinimaAprobacionCurso;
        
        // Verificar si aprobó todos los materiales requeridos
        $todosMaterilesAprobados = collect($detalles)->every(fn($m) => $m['aprobado']);
        
        return [
            'nota_final' => round($notaFinalCurso, 2),
            'nota_maxima' => self::NOTA_MAXIMA,
            'nota_minima_aprobacion' => $notaMinimaAprobacionCurso,
            'aprobado' => $aprobadoCurso && $todosMaterilesAprobados,
            'aprobado_por_nota' => $aprobadoCurso,
            'todos_materiales_aprobados' => $todosMaterilesAprobados,
            'porcentaje_evaluado' => $porcentajeTotalEvaluado,
            'materiales' => $detalles,
        ];
    }

    /**
     * Calcular el porcentaje efectivo de una actividad sobre el curso
     * 
     * @param CursoActividad $actividad
     * @return float
     */
    public function calcularPorcentajeEfectivoActividad(CursoActividad $actividad): float
    {
        if (!$actividad->material) {
            return 0;
        }
        
        // Porcentaje del material en el curso (0-100)
        $porcentajeMaterialEnCurso = floatval($actividad->material->porcentaje_curso);
        
        // Porcentaje de la actividad en el material (0-100)
        $porcentajeActividadEnMaterial = floatval($actividad->porcentaje_curso);
        
        // Porcentaje efectivo = (% material / 100) * (% actividad / 100) * 100
        return ($porcentajeMaterialEnCurso / 100) * ($porcentajeActividadEnMaterial / 100) * 100;
    }
}
