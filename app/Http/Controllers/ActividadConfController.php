<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

class ActividadConfController extends Controller
{
    public function index()
    {
        return view('admin.configuracion.actividades.index');
    }

    public function getData(Request $request): JsonResponse
    {
        $query = Actividad::select(['id', 'nombre', 'created_at', 'updated_at']);

        if ($request->filled('nombre')) {
            $query->where('nombre', 'like', '%' . $request->nombre . '%');
        }

        return DataTables::of($query)
            ->addColumn('fecha_creacion', function ($item) {
                return $item->created_at ? $item->created_at->format('d/m/Y H:i') : '-';
            })
            ->addColumn('actions', function ($item) {
                return '
                    <div class="d-flex" style="gap:4px;">
                        <button type="button" class="btn btn-info btn-sm shadow-sm" onclick="viewItem(' . $item->id . ')" title="Ver detalles" style="border-radius:6px;">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button type="button" class="btn btn-warning btn-sm shadow-sm" onclick="editItem(' . $item->id . ')" title="Editar" style="border-radius:6px;">
                            <i class="fas fa-pencil-alt"></i>
                        </button>
                        <button type="button" class="btn btn-danger btn-sm shadow-sm" onclick="deleteItem(' . $item->id . ')" title="Eliminar" style="border-radius:6px;">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:100|unique:actividades,nombre',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max'      => 'El nombre no puede tener más de 100 caracteres.',
            'nombre.unique'   => 'Esta actividad ya existe.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        Actividad::create($request->only('nombre'));

        return response()->json(['success' => true, 'message' => 'Actividad creada exitosamente.']);
    }

    public function show(Actividad $actividad): JsonResponse
    {
        return response()->json([
            'id'         => $actividad->id,
            'nombre'     => $actividad->nombre,
            'created_at' => $actividad->created_at ? $actividad->created_at->format('d/m/Y H:i') : '-',
            'updated_at' => $actividad->updated_at ? $actividad->updated_at->format('d/m/Y H:i') : '-',
        ]);
    }

    public function edit(Actividad $actividad): JsonResponse
    {
        return response()->json([
            'id'     => $actividad->id,
            'nombre' => $actividad->nombre,
        ]);
    }

    public function update(Request $request, Actividad $actividad)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:100|unique:actividades,nombre,' . $actividad->id,
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max'      => 'El nombre no puede tener más de 100 caracteres.',
            'nombre.unique'   => 'Esta actividad ya existe.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $actividad->update($request->only('nombre'));

        return response()->json(['success' => true, 'message' => 'Actividad actualizada exitosamente.']);
    }

    public function destroy(Actividad $actividad)
    {
        $actividad->delete();
        return response()->json(['success' => true, 'message' => 'Actividad eliminada exitosamente.']);
    }
}
