<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Gate;

class CategoriaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('categorias.view');
        return view('admin.capacitaciones.categorias.index');
    }

    /**
     * Get data for DataTables.
     */
    public function getData(Request $request): JsonResponse
    {
        $query = Categoria::select(['id', 'descripcion', 'created_at', 'updated_at']);

        // Aplicar filtro de descripción
        if ($request->filled('descripcion')) {
            $query->where('descripcion', 'like', '%' . $request->descripcion . '%');
        }

        return DataTables::of($query)
            ->addColumn('fecha_creacion', function ($categoria) {
                return $categoria->created_at->format('d/m/Y H:i');
            })
            ->addColumn('actions', function ($categoria) {
                $actions = '<div class="btn-group" role="group">';

                // Botón de ver
                $actions .= '<button type="button" class="btn btn-sm btn-info" onclick="viewCategoria(' . $categoria->id . ')" title="Ver detalles">
                    <i class="fas fa-eye"></i>
                </button>';

                // Botón de editar (solo si tiene permiso)
                if (Gate::allows('categorias.edit')) {
                    $actions .= '<button type="button" class="btn btn-sm btn-warning ml-1" onclick="editCategoria(' . $categoria->id . ')" title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>';
                }

                // Botón de eliminar (solo si tiene permiso)
                if (Gate::allows('categorias.delete')) {
                    $actions .= '<button type="button" class="btn btn-sm btn-danger ml-1" onclick="deleteCategoria(' . $categoria->id . ')" title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>';
                }

                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Gate::authorize('categorias.create');
        return view('admin.capacitaciones.categorias.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('categorias.create');
        $validator = Validator::make($request->all(), [
            'descripcion' => 'required|string|max:100|unique:categorias,descripcion',
        ], [
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.max' => 'La descripción no puede tener más de 100 caracteres.',
            'descripcion.unique' => 'Ya existe una categoría con esta descripción.',
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
            $categoria = Categoria::create([
                'descripcion' => $request->descripcion,
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Categoría creada exitosamente.',
                    'categoria' => $categoria
                ]);
            }

            return redirect()->route('capacitaciones.categorias.index')
                ->with('success', 'Categoría creada exitosamente.');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear la categoría: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Error al crear la categoría: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Categoria $categoria): JsonResponse
    {
        return response()->json([
            'id' => $categoria->id,
            'descripcion' => $categoria->descripcion,
            'created_at' => $categoria->created_at->format('d/m/Y H:i:s'),
            'updated_at' => $categoria->updated_at->format('d/m/Y H:i:s'),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Categoria $categoria)
    {
        Gate::authorize('categorias.edit');
        if (request()->ajax()) {
            return response()->json($categoria);
        }
        return view('admin.capacitaciones.categorias.edit', compact('categoria'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Categoria $categoria)
    {
        Gate::authorize('categorias.edit');
        $validator = Validator::make($request->all(), [
            'descripcion' => 'required|string|max:100|unique:categorias,descripcion,' . $categoria->id,
        ], [
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.max' => 'La descripción no puede tener más de 100 caracteres.',
            'descripcion.unique' => 'Ya existe una categoría con esta descripción.',
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
            $categoria->update([
                'descripcion' => $request->descripcion,
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Categoría actualizada exitosamente.',
                    'categoria' => $categoria
                ]);
            }

            return redirect()->route('capacitaciones.categorias.index')
                ->with('success', 'Categoría actualizada exitosamente.');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar la categoría: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Error al actualizar la categoría: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Categoria $categoria)
    {
        Gate::authorize('categorias.delete');
        try {
            $categoria->delete();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Categoría eliminada exitosamente.'
                ]);
            }

            return redirect()->route('capacitaciones.categorias.index')
                ->with('success', 'Categoría eliminada exitosamente.');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar la categoría: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Error al eliminar la categoría: ' . $e->getMessage());
        }
    }
}
