<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WelcomeBanner;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class AyudaController extends Controller
{
    /**
     * Asegura que los permisos de Ayuda existan en BD.
     * Se ejecuta una sola vez (auto-provisioning para producción/cPanel).
     */
    private function ensurePermissionsExist()
    {
        try {
            if (!Schema::hasTable('permissions')) return;

            $required = [
                ['name' => 'ayuda.view',   'display_name' => 'Ver Banners',      'group' => 'Ayuda'],
                ['name' => 'ayuda.create', 'display_name' => 'Crear Banner',     'group' => 'Ayuda'],
                ['name' => 'ayuda.edit',   'display_name' => 'Editar Banner',    'group' => 'Ayuda'],
                ['name' => 'ayuda.delete', 'display_name' => 'Eliminar Banner',  'group' => 'Ayuda'],
            ];

            $existing = \DB::table('permissions')->whereIn('name', array_column($required, 'name'))->pluck('name')->toArray();

            $toInsert = [];
            $now = now();
            foreach ($required as $p) {
                if (!in_array($p['name'], $existing)) {
                    $toInsert[] = array_merge($p, ['created_at' => $now, 'updated_at' => $now]);
                }
            }

            if (!empty($toInsert)) {
                \DB::table('permissions')->insert($toInsert);

                // Asignar automáticamente al Super Admin
                if (Schema::hasTable('role_permissions')) {
                    $newIds = \DB::table('permissions')->whereIn('name', array_column($toInsert, 'name'))->pluck('id');
                    $saInserts = [];
                    foreach ($newIds as $id) {
                        $saInserts[] = ['role_name' => 'Super Admin', 'permission_id' => $id, 'created_at' => $now, 'updated_at' => $now];
                    }
                    \DB::table('role_permissions')->insert($saInserts);
                }
            }
        } catch (\Throwable $e) {
            // Silenciar — no bloquear la página si falla
        }
    }

    /**
     * Asegura que la tabla welcome_banners exista, creándola si no.
     */
    private function ensureTableExists()
    {
        if (!Schema::hasTable('welcome_banners')) {
            Schema::create('welcome_banners', function (Blueprint $table) {
                $table->id();
                $table->string('banner_titulo')->nullable();
                $table->string('banner_subtitulo')->nullable();
                $table->string('banner_color_fondo', 20)->default('#2c4370');
                $table->string('banner_color_texto', 20)->default('#ffffff');
                $table->enum('media_tipo', ['video', 'imagen'])->default('video');
                $table->string('media_archivo')->nullable();
                $table->string('media_url')->nullable();
                $table->string('media_titulo')->nullable();
                $table->boolean('activo')->default(true);
                $table->integer('orden')->default(0);
                $table->date('fecha_inicio')->nullable();
                $table->date('fecha_fin')->nullable();
                $table->timestamps();
            });
        } else {
            // Agregar columnas nuevas si faltan (producción)
            if (!Schema::hasColumn('welcome_banners', 'fecha_inicio')) {
                Schema::table('welcome_banners', function (Blueprint $table) {
                    $table->date('fecha_inicio')->nullable()->after('orden');
                    $table->date('fecha_fin')->nullable()->after('fecha_inicio');
                });
            }
        }
    }

    /**
     * Muestra la vista principal de administración de banners.
     */
    public function index()
    {
        $this->ensurePermissionsExist();
        $this->ensureTableExists();
        $banners = WelcomeBanner::orderBy('orden')->get();
        return view('admin.configuracion.ayuda.index', compact('banners'));
    }

    /**
     * Almacena un nuevo banner.
     */
    public function store(Request $request)
    {
        $this->ensureTableExists();

        $request->validate([
            'banner_titulo'      => 'required|string|max:255',
            'banner_subtitulo'   => 'nullable|string|max:255',
            'banner_color_fondo' => 'nullable|string|max:20',
            'banner_color_texto' => 'nullable|string|max:20',
            'media_tipo'         => 'required|in:video,imagen',
            'media_archivo'      => 'nullable|file|mimes:mp4,webm,ogg,jpg,jpeg,png,gif,webp|max:51200',
            'media_url'          => 'nullable|url|max:500',
            'media_titulo'       => 'nullable|string|max:255',
            'fecha_inicio'       => 'nullable|date',
            'fecha_fin'          => 'nullable|date|after_or_equal:fecha_inicio',
        ]);

        try {
            $data = $request->except('media_archivo');
            $data['activo'] = $request->has('activo') ? 1 : 0;
            $data['orden'] = WelcomeBanner::max('orden') + 1;
            $data['fecha_inicio'] = $request->input('fecha_inicio') ?: null;
            $data['fecha_fin'] = $request->input('fecha_fin') ?: null;

            // Subir archivo si se proporciona
            if ($request->hasFile('media_archivo')) {
                $archivo = $request->file('media_archivo');
                $path = $archivo->store('welcome_banners', 'public');
                $data['media_archivo'] = $path;
            }

            WelcomeBanner::create($data);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Banner creado exitosamente.']);
            }

            return redirect()->route('configuracion.ayuda')
                ->with('success', 'Banner creado exitosamente.');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Error al crear el banner: ' . $e->getMessage()], 500);
            }
            return redirect()->route('configuracion.ayuda')
                ->with('error', 'Error al crear el banner: ' . $e->getMessage());
        }
    }

    /**
     * Actualiza un banner existente.
     */
    public function update(Request $request, $id)
    {
        $this->ensureTableExists();
        $banner = WelcomeBanner::findOrFail($id);

        $request->validate([
            'banner_titulo'      => 'required|string|max:255',
            'banner_subtitulo'   => 'nullable|string|max:255',
            'banner_color_fondo' => 'nullable|string|max:20',
            'banner_color_texto' => 'nullable|string|max:20',
            'media_tipo'         => 'required|in:video,imagen',
            'media_archivo'      => 'nullable|file|mimes:mp4,webm,ogg,jpg,jpeg,png,gif,webp|max:51200',
            'media_url'          => 'nullable|url|max:500',
            'media_titulo'       => 'nullable|string|max:255',
            'fecha_inicio'       => 'nullable|date',
            'fecha_fin'          => 'nullable|date|after_or_equal:fecha_inicio',
        ]);

        try {
            $data = $request->except(['media_archivo', '_method', '_token']);
            $data['activo'] = $request->has('activo') ? 1 : 0;
            $data['fecha_inicio'] = $request->input('fecha_inicio') ?: null;
            $data['fecha_fin'] = $request->input('fecha_fin') ?: null;

            // Subir nuevo archivo si se proporciona
            if ($request->hasFile('media_archivo')) {
                // Eliminar archivo anterior
                if ($banner->media_archivo) {
                    Storage::disk('public')->delete($banner->media_archivo);
                }
                $archivo = $request->file('media_archivo');
                $path = $archivo->store('welcome_banners', 'public');
                $data['media_archivo'] = $path;
            }

            $banner->update($data);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Banner actualizado exitosamente.']);
            }

            return redirect()->route('configuracion.ayuda')
                ->with('success', 'Banner actualizado exitosamente.');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Error al actualizar el banner: ' . $e->getMessage()], 500);
            }
            return redirect()->route('configuracion.ayuda')
                ->with('error', 'Error al actualizar el banner: ' . $e->getMessage());
        }
    }

    /**
     * Elimina un banner.
     */
    public function destroy($id)
    {
        $this->ensureTableExists();
        $banner = WelcomeBanner::findOrFail($id);

        // Eliminar archivo asociado
        if ($banner->media_archivo) {
            Storage::disk('public')->delete($banner->media_archivo);
        }

        $banner->delete();

        return redirect()->route('configuracion.ayuda')
            ->with('success', 'Banner eliminado exitosamente.');
    }

    /**
     * Activa/desactiva un banner.
     */
    public function toggleActivo($id)
    {
        $this->ensureTableExists();
        $banner = WelcomeBanner::findOrFail($id);
        $banner->activo = !$banner->activo;
        $banner->save();

        return response()->json(['success' => true, 'activo' => $banner->activo]);
    }

    /**
     * Actualiza el orden de los banners.
     */
    public function updateOrden(Request $request)
    {
        $this->ensureTableExists();
        $orden = $request->input('orden', []);
        foreach ($orden as $index => $id) {
            WelcomeBanner::where('id', $id)->update(['orden' => $index]);
        }

        return response()->json(['success' => true]);
    }
}
