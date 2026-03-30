<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class DashboardController extends Controller
{
    /**
     * Muestra el dashboard principal del sistema.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Obtener datos de publicidad y productos para el marketplace
        $productos = $this->getProductos();
        $categorias = $this->getCategorias();
        $configuracion = $this->getConfiguracion();
        
        // Obtener total de usuarios para el chat interno
        $totalUsuarios = User::where('id', '!=', auth()->id())->count();
        
        return view('dashboard', compact('productos', 'categorias', 'configuracion', 'totalUsuarios'));
    }

    /**
     * Buscar estudiantes por nombre o ID para el chat interno
     */
    public function buscarEstudiantes(Request $request)
    {
        $query = $request->input('query', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }
        
        $estudiantes = User::where(function($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('email', 'LIKE', "%{$query}%")
                  ->orWhere('numero_documento', 'LIKE', "%{$query}%")
                  ->orWhere('id', $query);
            })
            ->where('id', '!=', auth()->id()) // Excluir al usuario actual
            ->select('id', 'name', 'apellido1', 'apellido2', 'email', 'numero_documento')
            ->limit(10)
            ->get()
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'nombre' => $user->full_name,
                    'email' => $user->email,
                    'documento' => $user->numero_documento,
                ];
            });
        
        return response()->json($estudiantes);
    }

    /**
     * Enviar mensaje de chat
     */
    public function enviarMensaje(Request $request)
    {
        $request->validate([
            'mensaje' => 'required|string|max:4000',
            'destinatario_id' => 'nullable|exists:users,id',
            'es_difusion' => 'boolean',
        ]);

        $esDifusion = $request->boolean('es_difusion');

        if ($esDifusion) {
            // Difusión masiva: enviar a todos los usuarios excepto el remitente
            $usuarios = User::where('id', '!=', auth()->id())->get();
            
            foreach ($usuarios as $usuario) {
                \App\Models\MensajeChat::create([
                    'remitente_id' => auth()->id(),
                    'destinatario_id' => $usuario->id,
                    'mensaje' => $request->mensaje,
                    'es_difusion' => true,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Mensaje enviado a ' . $usuarios->count() . ' usuarios',
                'count' => $usuarios->count(),
            ]);
        } else {
            // Mensaje individual
            if (!$request->destinatario_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debe seleccionar un destinatario',
                ], 422);
            }

            $mensaje = \App\Models\MensajeChat::create([
                'remitente_id' => auth()->id(),
                'destinatario_id' => $request->destinatario_id,
                'mensaje' => $request->mensaje,
                'es_difusion' => false,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Mensaje enviado correctamente',
                'mensaje' => $mensaje,
            ]);
        }
    }

    /**
     * Obtener mensajes del chat
     */
    public function obtenerMensajes(Request $request)
    {
        $destinatarioId = $request->input('destinatario_id');
        
        if ($destinatarioId) {
            // Conversación con un usuario específico
            $mensajes = \App\Models\MensajeChat::where(function($q) use ($destinatarioId) {
                    $q->where('remitente_id', auth()->id())
                      ->where('destinatario_id', $destinatarioId);
                })
                ->orWhere(function($q) use ($destinatarioId) {
                    $q->where('remitente_id', $destinatarioId)
                      ->where('destinatario_id', auth()->id());
                })
                ->with(['remitente', 'destinatario'])
                ->orderBy('created_at', 'asc')
                ->get();

            // Marcar como leídos los mensajes recibidos
            \App\Models\MensajeChat::where('remitente_id', $destinatarioId)
                ->where('destinatario_id', auth()->id())
                ->where('leido', false)
                ->update(['leido' => true, 'leido_at' => now()]);
        } else {
            // Todos los mensajes recibidos
            $mensajes = \App\Models\MensajeChat::where('destinatario_id', auth()->id())
                ->with(['remitente'])
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get();
        }

        return response()->json($mensajes);
    }

    /**
     * Obtener conversaciones (lista de usuarios con los que se ha chateado)
     */
    public function obtenerConversaciones()
    {
        $userId = auth()->id();

        // Obtener IDs únicos de usuarios con los que se ha conversado
        $conversacionesIds = \App\Models\MensajeChat::where('remitente_id', $userId)
            ->orWhere('destinatario_id', $userId)
            ->get()
            ->map(function($mensaje) use ($userId) {
                return $mensaje->remitente_id == $userId 
                    ? $mensaje->destinatario_id 
                    : $mensaje->remitente_id;
            })
            ->unique()
            ->filter();

        // Obtener información de los usuarios y último mensaje
        $conversaciones = User::whereIn('id', $conversacionesIds)
            ->get()
            ->map(function($usuario) use ($userId) {
                $ultimoMensaje = \App\Models\MensajeChat::where(function($q) use ($userId, $usuario) {
                        $q->where('remitente_id', $userId)
                          ->where('destinatario_id', $usuario->id);
                    })
                    ->orWhere(function($q) use ($userId, $usuario) {
                        $q->where('remitente_id', $usuario->id)
                          ->where('destinatario_id', $userId);
                    })
                    ->orderBy('created_at', 'desc')
                    ->first();

                $noLeidos = \App\Models\MensajeChat::where('remitente_id', $usuario->id)
                    ->where('destinatario_id', $userId)
                    ->where('leido', false)
                    ->count();

                return [
                    'id' => $usuario->id,
                    'nombre' => $usuario->full_name,
                    'email' => $usuario->email,
                    'ultimo_mensaje' => $ultimoMensaje ? $ultimoMensaje->mensaje : null,
                    'ultimo_mensaje_fecha' => $ultimoMensaje ? $ultimoMensaje->created_at->diffForHumans() : null,
                    'no_leidos' => $noLeidos,
                ];
            })
            ->sortByDesc('ultimo_mensaje_fecha');

        return response()->json($conversaciones->values());
    }

    /**
     * Obtener productos/cursos promocionados
     */
    private function getProductos(): array
    {
        $path = storage_path('app/publicidad_productos.json');
        if (file_exists($path)) {
            $productos = json_decode(file_get_contents($path), true) ?? [];
            // Ordenar por orden y filtrar solo activos/destacados
            usort($productos, fn($a, $b) => ($a['orden'] ?? 999) - ($b['orden'] ?? 999));
            return array_filter($productos, fn($p) => $p['estado'] !== 'inactivo');
        }
        return $this->getProductosDefault();
    }

    /**
     * Obtener categorías
     */
    private function getCategorias(): array
    {
        $path = storage_path('app/publicidad_categorias.json');
        if (file_exists($path)) {
            return json_decode(file_get_contents($path), true) ?? [];
        }
        return $this->getCategoriasDefault();
    }

    /**
     * Obtener configuración del banner
     */
    private function getConfiguracion(): array
    {
        $path = storage_path('app/publicidad_config.json');
        if (file_exists($path)) {
            return json_decode(file_get_contents($path), true) ?? [];
        }
        return $this->getConfiguracionDefault();
    }

    /**
     * Productos por defecto
     */
    private function getProductosDefault(): array
    {
        return [
            [
                'id' => 1,
                'titulo' => 'Curso de Capacitación en Salud',
                'descripcion' => 'Formación integral para profesionales de la salud con certificación oficial.',
                'categoria' => 'Capacitaciones',
                'precio' => null,
                'imagen' => null,
                'url_externa' => null,
                'estado' => 'destacado',
                'orden' => 1,
            ],
            [
                'id' => 2,
                'titulo' => 'Diplomado en Gestión Hospitalaria',
                'descripcion' => 'Aprende las mejores prácticas en administración y gestión de servicios de salud.',
                'categoria' => 'Diplomados',
                'precio' => null,
                'imagen' => null,
                'url_externa' => null,
                'estado' => 'activo',
                'orden' => 2,
            ],
            [
                'id' => 3,
                'titulo' => 'Taller de Primeros Auxilios',
                'descripcion' => 'Capacitación práctica en técnicas de primeros auxilios y emergencias.',
                'categoria' => 'Talleres',
                'precio' => null,
                'imagen' => null,
                'url_externa' => null,
                'estado' => 'activo',
                'orden' => 3,
            ],
            [
                'id' => 4,
                'titulo' => 'Seminario de Actualización Médica',
                'descripcion' => 'Últimas tendencias y avances en el campo de la medicina.',
                'categoria' => 'Seminarios',
                'precio' => null,
                'imagen' => null,
                'url_externa' => null,
                'estado' => 'activo',
                'orden' => 4,
            ],
        ];
    }

    /**
     * Categorías por defecto
     */
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

    /**
     * Configuración por defecto
     */
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
