<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class PublicidadProductoController extends Controller
{
    /**
     * Mostrar la vista de administración de publicidad y productos
     */
    public function index()
    {
        Gate::authorize('publicidad.view');
        $productos = $this->getProductos();
        $categorias = $this->getCategorias();
        $configuracion = $this->getConfiguracion();
        
        return view('admin.configuracion.publicidad-productos.index', compact('productos', 'categorias', 'configuracion'));
    }

    /**
     * Obtener datos para DataTables
     */
    public function getData(Request $request)
    {
        $productos = $this->getProductos();
        
        return datatables()->of($productos)
            ->addColumn('imagen_html', function($producto) {
                if ($producto['imagen']) {
                    return '<img src="' . asset('storage/' . $producto['imagen']) . '" class="img-thumbnail" style="max-width: 60px;">';
                }
                return '<span class="badge badge-secondary">Sin imagen</span>';
            })
            ->addColumn('estado_badge', function($producto) {
                $badges = [
                    'activo' => '<span class="badge badge-success">Activo</span>',
                    'inactivo' => '<span class="badge badge-secondary">Inactivo</span>',
                    'destacado' => '<span class="badge badge-warning">Destacado</span>',
                ];
                return $badges[$producto['estado']] ?? '<span class="badge badge-secondary">-</span>';
            })
            ->addColumn('acciones', function($producto) {
                $html = '';
                if (Gate::allows('publicidad.edit')) {
                    $html .= '<button class="btn btn-sm btn-info btn-editar" data-id="' . $producto['id'] . '">
                        <i class="fas fa-edit"></i>
                    </button> ';
                }
                if (Gate::allows('publicidad.delete')) {
                    $html .= '<button class="btn btn-sm btn-danger btn-eliminar" data-id="' . $producto['id'] . '">
                        <i class="fas fa-trash"></i>
                    </button>';
                }
                return $html;
            })
            ->rawColumns(['imagen_html', 'estado_badge', 'acciones'])
            ->make(true);
    }

    /**
     * Guardar nuevo producto/publicidad
     */
    public function store(Request $request)
    {
        Gate::authorize('publicidad.create');
        $validator = Validator::make($request->all(), [
            'titulo' => 'required|string|max:200',
            'descripcion' => 'nullable|string',
            'categoria' => 'required|string|max:100',
            'precio' => 'nullable|numeric|min:0',
            'imagen' => 'nullable|image|max:5120',
            'url_externa' => 'nullable|url',
            'estado' => 'required|in:activo,inactivo,destacado',
            'orden' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $productos = $this->getProductos();
            
            $nuevoProducto = [
                'id' => time(),
                'titulo' => $request->titulo,
                'descripcion' => $request->descripcion,
                'categoria' => $request->categoria,
                'precio' => $request->precio,
                'url_externa' => $request->url_externa,
                'estado' => $request->estado,
                'orden' => $request->orden ?? count($productos) + 1,
                'imagen' => null,
                'created_at' => now()->toDateTimeString(),
            ];

            // Manejar imagen
            if ($request->hasFile('imagen')) {
                $file = $request->file('imagen');
                $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('publicidad', $fileName, 'public');
                $nuevoProducto['imagen'] = $path;
            }

            $productos[] = $nuevoProducto;
            $this->saveProductos($productos);

            return response()->json([
                'success' => true,
                'message' => 'Producto agregado exitosamente',
                'producto' => $nuevoProducto
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar producto existente
     */
    public function update(Request $request, $id)
    {
        Gate::authorize('publicidad.edit');
        $validator = Validator::make($request->all(), [
            'titulo' => 'required|string|max:200',
            'descripcion' => 'nullable|string',
            'categoria' => 'required|string|max:100',
            'precio' => 'nullable|numeric|min:0',
            'imagen' => 'nullable|image|max:5120',
            'url_externa' => 'nullable|url',
            'estado' => 'required|in:activo,inactivo,destacado',
            'orden' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $productos = $this->getProductos();
            $index = array_search($id, array_column($productos, 'id'));

            if ($index === false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Producto no encontrado'
                ], 404);
            }

            $productos[$index]['titulo'] = $request->titulo;
            $productos[$index]['descripcion'] = $request->descripcion;
            $productos[$index]['categoria'] = $request->categoria;
            $productos[$index]['precio'] = $request->precio;
            $productos[$index]['url_externa'] = $request->url_externa;
            $productos[$index]['estado'] = $request->estado;
            $productos[$index]['orden'] = $request->orden ?? $productos[$index]['orden'];
            $productos[$index]['updated_at'] = now()->toDateTimeString();

            // Manejar nueva imagen
            if ($request->hasFile('imagen')) {
                // Eliminar imagen anterior
                if ($productos[$index]['imagen']) {
                    Storage::disk('public')->delete($productos[$index]['imagen']);
                }
                
                $file = $request->file('imagen');
                $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('publicidad', $fileName, 'public');
                $productos[$index]['imagen'] = $path;
            }

            $this->saveProductos($productos);

            return response()->json([
                'success' => true,
                'message' => 'Producto actualizado exitosamente',
                'producto' => $productos[$index]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar producto
     */
    public function destroy($id)
    {
        Gate::authorize('publicidad.delete');
        try {
            $productos = $this->getProductos();
            $index = array_search($id, array_column($productos, 'id'));

            if ($index === false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Producto no encontrado'
                ], 404);
            }

            // Eliminar imagen si existe
            if ($productos[$index]['imagen']) {
                Storage::disk('public')->delete($productos[$index]['imagen']);
            }

            array_splice($productos, $index, 1);
            $this->saveProductos($productos);

            return response()->json([
                'success' => true,
                'message' => 'Producto eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Guardar configuración del banner
     */
    public function guardarConfiguracion(Request $request)
    {
        Gate::authorize('publicidad.banner');
        $validator = Validator::make($request->all(), [
            'banner_titulo' => 'nullable|string|max:200',
            'banner_subtitulo' => 'nullable|string|max:500',
            'banner_imagen' => 'nullable|image|max:10240',
            'banner_url_imagen' => 'nullable|url',
            'mostrar_categorias' => 'boolean',
            'mostrar_seccion_vendedor' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $config = $this->getConfiguracion();
            
            $config['banner_titulo'] = $request->banner_titulo ?? $config['banner_titulo'];
            $config['banner_subtitulo'] = $request->banner_subtitulo ?? $config['banner_subtitulo'];
            $config['mostrar_categorias'] = $request->boolean('mostrar_categorias', true);
            $config['mostrar_seccion_vendedor'] = $request->boolean('mostrar_seccion_vendedor', true);

            if ($request->hasFile('banner_imagen')) {
                if ($config['banner_imagen'] && !str_starts_with($config['banner_imagen'], 'http')) {
                    Storage::disk('public')->delete($config['banner_imagen']);
                }
                
                $file = $request->file('banner_imagen');
                $fileName = 'banner_' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('publicidad', $fileName, 'public');
                $config['banner_imagen'] = $path;
            } elseif ($request->banner_url_imagen) {
                $config['banner_imagen'] = $request->banner_url_imagen;
            }

            $this->saveConfiguracion($config);

            return response()->json([
                'success' => true,
                'message' => 'Configuración guardada exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar configuración: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Guardar categorías
     */
    public function guardarCategorias(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'categorias' => 'required|array',
            'categorias.*.nombre' => 'required|string|max:100',
            'categorias.*.icono' => 'required|string|max:50',
            'categorias.*.activo' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $this->saveCategorias($request->categorias);

            return response()->json([
                'success' => true,
                'message' => 'Categorías guardadas exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar categorías: ' . $e->getMessage()
            ], 500);
        }
    }

    // ==========================================
    // MÉTODOS PRIVADOS PARA MANEJO DE DATOS
    // ==========================================

    private function getProductos(): array
    {
        $path = storage_path('app/publicidad_productos.json');
        if (file_exists($path)) {
            return json_decode(file_get_contents($path), true) ?? [];
        }
        return $this->getProductosDefault();
    }

    private function saveProductos(array $productos): void
    {
        $path = storage_path('app/publicidad_productos.json');
        file_put_contents($path, json_encode($productos, JSON_PRETTY_PRINT));
    }

    private function getCategorias(): array
    {
        $path = storage_path('app/publicidad_categorias.json');
        if (file_exists($path)) {
            return json_decode(file_get_contents($path), true) ?? [];
        }
        return $this->getCategoriasDefault();
    }

    private function saveCategorias(array $categorias): void
    {
        $path = storage_path('app/publicidad_categorias.json');
        file_put_contents($path, json_encode($categorias, JSON_PRETTY_PRINT));
    }

    private function getConfiguracion(): array
    {
        $path = storage_path('app/publicidad_config.json');
        if (file_exists($path)) {
            return json_decode(file_get_contents($path), true) ?? [];
        }
        return $this->getConfiguracionDefault();
    }

    private function saveConfiguracion(array $config): void
    {
        $path = storage_path('app/publicidad_config.json');
        file_put_contents($path, json_encode($config, JSON_PRETTY_PRINT));
    }

    private function getProductosDefault(): array
    {
        return [
            [
                'id' => 1,
                'titulo' => 'Curso de Capacitación en Salud',
                'descripcion' => 'Formación integral para profesionales de la salud',
                'categoria' => 'Capacitaciones',
                'precio' => null,
                'imagen' => null,
                'url_externa' => null,
                'estado' => 'destacado',
                'orden' => 1,
                'created_at' => now()->toDateTimeString(),
            ],
            [
                'id' => 2,
                'titulo' => 'Diplomado en Gestión Hospitalaria',
                'descripcion' => 'Aprende las mejores prácticas en administración de salud',
                'categoria' => 'Diplomados',
                'precio' => null,
                'imagen' => null,
                'url_externa' => null,
                'estado' => 'activo',
                'orden' => 2,
                'created_at' => now()->toDateTimeString(),
            ],
        ];
    }

    private function getCategoriasDefault(): array
    {
        return [
            ['nombre' => 'Todos', 'icono' => 'grid_view', 'activo' => true],
            ['nombre' => 'Capacitaciones', 'icono' => 'school', 'activo' => true],
            ['nombre' => 'Diplomados', 'icono' => 'workspace_premium', 'activo' => true],
            ['nombre' => 'Cursos', 'icono' => 'auto_stories', 'activo' => true],
            ['nombre' => 'Talleres', 'icono' => 'construction', 'activo' => true],
            ['nombre' => 'Seminarios', 'icono' => 'groups', 'activo' => true],
        ];
    }

    private function getConfiguracionDefault(): array
    {
        return [
            'banner_titulo' => 'Descubre tu próximo curso,<br/>impulsa tu carrera.',
            'banner_subtitulo' => 'Únete a miles de profesionales de la salud. La forma más fácil de capacitarte y crecer profesionalmente.',
            'banner_imagen' => 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1f?w=1200',
            'mostrar_categorias' => true,
            'mostrar_seccion_vendedor' => true,
        ];
    }
}
