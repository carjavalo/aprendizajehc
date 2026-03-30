<?php

namespace App\Http\Controllers;

use App\Models\VinculacionContrato;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

class VinculacionContratoController extends Controller
{
    public function index()
    {
        return view('admin.configuracion.componentes.vinculacion-contrato.index');
    }

    public function getData(Request $request): JsonResponse
    {
        $query = VinculacionContrato::select(['id', 'nombre', 'created_at', 'updated_at']);

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
            'nombre' => 'required|string|max:100|unique:vinculacion_contrato,nombre',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede tener más de 100 caracteres.',
            'nombre.unique' => 'Este nombre ya existe.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        VinculacionContrato::create($request->only('nombre'));

        return response()->json(['success' => true, 'message' => 'Vinculación/Contrato creado exitosamente.']);
    }

    public function show(VinculacionContrato $vinculacion_contrato): JsonResponse
    {
        return response()->json([
            'id' => $vinculacion_contrato->id,
            'nombre' => $vinculacion_contrato->nombre,
            'created_at' => $vinculacion_contrato->created_at ? $vinculacion_contrato->created_at->format('d/m/Y H:i') : '-',
            'updated_at' => $vinculacion_contrato->updated_at ? $vinculacion_contrato->updated_at->format('d/m/Y H:i') : '-',
        ]);
    }

    public function edit(VinculacionContrato $vinculacion_contrato): JsonResponse
    {
        return response()->json([
            'id' => $vinculacion_contrato->id,
            'nombre' => $vinculacion_contrato->nombre,
        ]);
    }

    public function update(Request $request, VinculacionContrato $vinculacion_contrato)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:100|unique:vinculacion_contrato,nombre,' . $vinculacion_contrato->id,
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede tener más de 100 caracteres.',
            'nombre.unique' => 'Este nombre ya existe.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $vinculacion_contrato->update($request->only('nombre'));

        return response()->json(['success' => true, 'message' => 'Vinculación/Contrato actualizado exitosamente.']);
    }

    public function destroy(VinculacionContrato $vinculacion_contrato)
    {
        $vinculacion_contrato->delete();
        return response()->json(['success' => true, 'message' => 'Vinculación/Contrato eliminado exitosamente.']);
    }
}
