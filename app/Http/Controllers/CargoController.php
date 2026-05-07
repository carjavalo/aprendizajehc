<?php

namespace App\Http\Controllers;

use App\Models\Cargo;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

class CargoController extends Controller
{
    public function index()
    {
        return view('admin.configuracion.cargos.index');
    }

    public function getData(Request $request): JsonResponse
    {
        $query = Cargo::select(['id', 'nombre', 'created_at', 'updated_at']);

        if ($request->filled('nombre')) {
            $query->where('nombre', 'like', '%' . $request->nombre . '%');
        }

        return DataTables::of($query)
            ->addColumn('fecha_creacion', function ($item) {
                return $item->created_at ? $item->created_at->format('d/m/Y H:i') : '-';
            })
            ->addColumn('actions', function ($item) {
                return '
                    <div class="d-flex gap-1" style="gap:4px;">
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
            'nombre' => 'required|string|max:100|unique:cargos,nombre',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max'      => 'El nombre no puede tener más de 100 caracteres.',
            'nombre.unique'   => 'Este Cargo/Especialidad ya existe.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        Cargo::create($request->only('nombre'));

        return response()->json(['success' => true, 'message' => 'Cargo/Especialidad creado exitosamente.']);
    }

    public function show(Cargo $cargo): JsonResponse
    {
        return response()->json([
            'id'         => $cargo->id,
            'nombre'     => $cargo->nombre,
            'created_at' => $cargo->created_at ? $cargo->created_at->format('d/m/Y H:i') : '-',
            'updated_at' => $cargo->updated_at ? $cargo->updated_at->format('d/m/Y H:i') : '-',
        ]);
    }

    public function edit(Cargo $cargo): JsonResponse
    {
        return response()->json([
            'id'     => $cargo->id,
            'nombre' => $cargo->nombre,
        ]);
    }

    public function update(Request $request, Cargo $cargo)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:100|unique:cargos,nombre,' . $cargo->id,
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max'      => 'El nombre no puede tener más de 100 caracteres.',
            'nombre.unique'   => 'Este Cargo/Especialidad ya existe.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $cargo->update($request->only('nombre'));

        return response()->json(['success' => true, 'message' => 'Cargo/Especialidad actualizado exitosamente.']);
    }

    public function destroy(Cargo $cargo)
    {
        $cargo->delete();
        return response()->json(['success' => true, 'message' => 'Cargo/Especialidad eliminado exitosamente.']);
    }
}
