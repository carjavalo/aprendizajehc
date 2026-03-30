<?php

namespace App\Http\Controllers;

use App\Models\ServicioArea;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

class ServicioAreaController extends Controller
{
    public function index()
    {
        return view('admin.configuracion.componentes.servicios-areas.index');
    }

    public function getData(Request $request): JsonResponse
    {
        $query = ServicioArea::select(['id', 'nombre', 'created_at', 'updated_at']);

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
            'nombre' => 'required|string|max:100|unique:servicios_areas,nombre',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede tener más de 100 caracteres.',
            'nombre.unique' => 'Este nombre ya existe.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        ServicioArea::create($request->only('nombre'));

        return response()->json(['success' => true, 'message' => 'Servicio/Área creado exitosamente.']);
    }

    public function show(ServicioArea $servicios_area): JsonResponse
    {
        return response()->json([
            'id' => $servicios_area->id,
            'nombre' => $servicios_area->nombre,
            'created_at' => $servicios_area->created_at ? $servicios_area->created_at->format('d/m/Y H:i') : '-',
            'updated_at' => $servicios_area->updated_at ? $servicios_area->updated_at->format('d/m/Y H:i') : '-',
        ]);
    }

    public function edit(ServicioArea $servicios_area): JsonResponse
    {
        return response()->json([
            'id' => $servicios_area->id,
            'nombre' => $servicios_area->nombre,
        ]);
    }

    public function update(Request $request, ServicioArea $servicios_area)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:100|unique:servicios_areas,nombre,' . $servicios_area->id,
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede tener más de 100 caracteres.',
            'nombre.unique' => 'Este nombre ya existe.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $servicios_area->update($request->only('nombre'));

        return response()->json(['success' => true, 'message' => 'Servicio/Área actualizado exitosamente.']);
    }

    public function destroy(ServicioArea $servicios_area)
    {
        $servicios_area->delete();
        return response()->json(['success' => true, 'message' => 'Servicio/Área eliminado exitosamente.']);
    }
}
