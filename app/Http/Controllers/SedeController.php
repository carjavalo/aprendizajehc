<?php

namespace App\Http\Controllers;

use App\Models\Sede;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

class SedeController extends Controller
{
    public function index()
    {
        return view('admin.configuracion.componentes.sedes.index');
    }

    public function getData(Request $request): JsonResponse
    {
        $query = Sede::select(['id', 'nombre', 'created_at', 'updated_at']);

        if ($request->filled('nombre')) {
            $query->where('nombre', 'like', '%' . $request->nombre . '%');
        }

        return DataTables::of($query)
            ->addColumn('fecha_creacion', function ($item) {
                return $item->created_at ? $item->created_at->format('d/m/Y H:i') : '-';
            })
            ->addColumn('actions', function ($item) {
                return '
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-info btn-sm" onclick="viewItem(' . $item->id . ')" title="Ver">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button type="button" class="btn btn-warning btn-sm" onclick="editItem(' . $item->id . ')" title="Editar">
                            <i class="fas fa-pencil-alt"></i>
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" onclick="deleteItem(' . $item->id . ')" title="Eliminar">
                            <i class="fas fa-trash"></i>
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
            'nombre' => 'required|string|max:100|unique:sedes,nombre',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede tener más de 100 caracteres.',
            'nombre.unique' => 'Este nombre ya existe.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        Sede::create($request->only('nombre'));

        return response()->json(['success' => true, 'message' => 'Sede creada exitosamente.']);
    }

    public function show(Sede $sede): JsonResponse
    {
        return response()->json([
            'id' => $sede->id,
            'nombre' => $sede->nombre,
            'created_at' => $sede->created_at ? $sede->created_at->format('d/m/Y H:i') : '-',
            'updated_at' => $sede->updated_at ? $sede->updated_at->format('d/m/Y H:i') : '-',
        ]);
    }

    public function edit(Sede $sede): JsonResponse
    {
        return response()->json([
            'id' => $sede->id,
            'nombre' => $sede->nombre,
        ]);
    }

    public function update(Request $request, Sede $sede)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:100|unique:sedes,nombre,' . $sede->id,
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede tener más de 100 caracteres.',
            'nombre.unique' => 'Este nombre ya existe.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $sede->update($request->only('nombre'));

        return response()->json(['success' => true, 'message' => 'Sede actualizada exitosamente.']);
    }

    public function destroy(Sede $sede)
    {
        $sede->delete();
        return response()->json(['success' => true, 'message' => 'Sede eliminada exitosamente.']);
    }
}
