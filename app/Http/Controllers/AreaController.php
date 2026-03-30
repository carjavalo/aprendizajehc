<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Gate;

class AreaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('areas.view');
        $categorias = Categoria::orderBy('descripcion')->get();
        return view('admin.capacitaciones.areas.index', compact('categorias'));
    }

    /**
     * Get data for DataTables.
     */
    public function getData(Request $request): JsonResponse
    {
        $query = Area::with('categoria')
                    ->select(['id', 'descripcion', 'cod_categoria', 'created_at', 'updated_at']);

        // Aplicar filtro de descripción
        if ($request->filled('descripcion')) {
            $query->where('descripcion', 'like', '%' . $request->descripcion . '%');
        }

        // Aplicar filtro de categoría
        if ($request->filled('categoria')) {
            $query->where('cod_categoria', $request->categoria);
        }

        return DataTables::of($query)
            ->addColumn('categoria_descripcion', function ($area) {
                return $area->categoria ? $area->categoria->descripcion : 'Sin categoría';
            })
            ->addColumn('fecha_creacion', function ($area) {
                return $area->created_at->format('d/m/Y H:i');
            })
            ->addColumn('actions', function ($area) {
                $html = '<div class="btn-group" role="group">';
                $html .= '<button type="button" class="btn btn-info btn-sm" onclick="viewArea(' . $area->id . ')" title="Ver">
                            <i class="fas fa-eye"></i>
                        </button>';
                if (Gate::allows('areas.edit')) {
                    $html .= '<button type="button" class="btn btn-warning btn-sm" onclick="editArea(' . $area->id . ')" title="Editar">
                            <i class="fas fa-pencil-alt"></i>
                        </button>';
                }
                if (Gate::allows('areas.delete')) {
                    $html .= '<button type="button" class="btn btn-danger btn-sm" onclick="deleteArea(' . $area->id . ')" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>';
                }
                $html .= '</div>';
                return $html;
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Gate::authorize('areas.create');
        $categorias = Categoria::orderBy('descripcion')->get();
        return response()->json(['categorias' => $categorias]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('areas.create');
        $validator = Validator::make($request->all(), [
            'descripcion' => 'required|string|max:100',
            'cod_categoria' => 'required|exists:categorias,id',
        ], [
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.max' => 'La descripción no puede tener más de 100 caracteres.',
            'cod_categoria.required' => 'La categoría es obligatoria.',
            'cod_categoria.exists' => 'La categoría seleccionada no es válida.',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            // Verificar que la tabla areas existe
            if (!\Schema::hasTable('areas')) {
                throw new \Exception('La tabla areas no existe. Ejecute las migraciones.');
            }

            // Verificar que la categoría existe
            $categoria = \App\Models\Categoria::find($request->cod_categoria);
            if (!$categoria) {
                throw new \Exception('La categoría seleccionada no existe.');
            }

            // Log para debugging
            \Log::info('Intentando crear área', [
                'descripcion' => $request->descripcion,
                'cod_categoria' => $request->cod_categoria,
                'categoria_existe' => $categoria ? 'Sí' : 'No'
            ]);

            $area = Area::create($request->only(['descripcion', 'cod_categoria']));

            \Log::info('Área creada exitosamente', ['area_id' => $area->id]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Área creada exitosamente.',
                    'data' => $area
                ]);
            }

            return redirect()->route('capacitaciones.areas.index')
                           ->with('success', 'Área creada exitosamente.');
        } catch (\Exception $e) {
            // Log detallado del error
            \Log::error('Error al crear área', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear el área: ' . $e->getMessage(),
                    'debug_info' => [
                        'file' => basename($e->getFile()),
                        'line' => $e->getLine(),
                        'request_data' => $request->all()
                    ]
                ], 500);
            }

            return redirect()->back()
                           ->with('error', 'Error al crear el área: ' . $e->getMessage())
                           ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Area $area)
    {
        $area->load('categoria');
        
        return response()->json([
            'id' => $area->id,
            'descripcion' => $area->descripcion,
            'categoria' => $area->categoria ? $area->categoria->descripcion : 'Sin categoría',
            'cod_categoria' => $area->cod_categoria,
            'created_at' => $area->created_at->format('d/m/Y H:i'),
            'updated_at' => $area->updated_at->format('d/m/Y H:i'),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Area $area)
    {
        Gate::authorize('areas.edit');
        $categorias = Categoria::orderBy('descripcion')->get();
        
        return response()->json([
            'id' => $area->id,
            'descripcion' => $area->descripcion,
            'cod_categoria' => $area->cod_categoria,
            'categorias' => $categorias
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Area $area)
    {
        Gate::authorize('areas.edit');
        $validator = Validator::make($request->all(), [
            'descripcion' => 'required|string|max:100',
            'cod_categoria' => 'required|exists:categorias,id',
        ], [
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.max' => 'La descripción no puede tener más de 100 caracteres.',
            'cod_categoria.required' => 'La categoría es obligatoria.',
            'cod_categoria.exists' => 'La categoría seleccionada no es válida.',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            // Verificar que la categoría existe
            $categoria = \App\Models\Categoria::find($request->cod_categoria);
            if (!$categoria) {
                throw new \Exception('La categoría seleccionada no existe.');
            }

            // Log para debugging
            \Log::info('Intentando actualizar área', [
                'area_id' => $area->id,
                'descripcion' => $request->descripcion,
                'cod_categoria' => $request->cod_categoria
            ]);

            $area->update($request->only(['descripcion', 'cod_categoria']));

            \Log::info('Área actualizada exitosamente', ['area_id' => $area->id]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Área actualizada exitosamente.',
                    'data' => $area
                ]);
            }

            return redirect()->route('capacitaciones.areas.index')
                           ->with('success', 'Área actualizada exitosamente.');
        } catch (\Exception $e) {
            // Log detallado del error
            \Log::error('Error al actualizar área', [
                'area_id' => $area->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'request_data' => $request->all()
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar el área: ' . $e->getMessage(),
                    'debug_info' => [
                        'file' => basename($e->getFile()),
                        'line' => $e->getLine()
                    ]
                ], 500);
            }

            return redirect()->back()
                           ->with('error', 'Error al actualizar el área: ' . $e->getMessage())
                           ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Area $area)
    {
        Gate::authorize('areas.delete');
        try {
            $area->delete();

            return response()->json([
                'success' => true,
                'message' => 'Área eliminada exitosamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el área: ' . $e->getMessage()
            ], 500);
        }
    }
}
