<?php

namespace App\Console\Commands;

use App\Models\GaleriaImagen;
use App\Models\Producto;
use Illuminate\Console\Command;

class AsignarImagenesDesdeGaleria extends Command
{
    protected $signature = 'productos:asignar-imagenes
                            {--tenant= : Limitar a un tenant específico}
                            {--dry-run : Solo mostrar qué se asignaría sin guardar}';

    protected $description = 'Busca en la galería imágenes que coincidan con el nombre de cada producto sin imagen y las asigna';

    public function handle(): int
    {
        $dryRun  = $this->option('dry-run');
        $tenantId = $this->option('tenant');

        if ($dryRun) {
            $this->warn('Modo DRY-RUN: no se guardarán cambios.');
        }

        // Obtener productos sin imagen ignorando global scopes (multi-tenant)
        $query = Producto::withoutGlobalScopes()
            ->whereNull('imagen')
            ->orWhere('imagen', '');

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        $productos = $query->orderBy('tenant_id')->orderBy('nombre')->get();

        if ($productos->isEmpty()) {
            $this->info('Todos los productos ya tienen imagen asignada.');
            return self::SUCCESS;
        }

        $this->info("Productos sin imagen encontrados: {$productos->count()}");

        // Pre-cargar toda la galería para evitar N+1
        $galeria = GaleriaImagen::all()->keyBy(fn ($img) => mb_strtolower($img->nombre));

        $asignados  = 0;
        $sinMatch   = 0;

        $bar = $this->output->createProgressBar($productos->count());
        $bar->start();

        foreach ($productos as $producto) {
            $nombreLower = mb_strtolower($producto->nombre);

            // 1. Coincidencia exacta por nombre
            $imagen = $galeria->get($nombreLower);

            // 2. Si no hay exacta, buscar en tags (búsqueda en memoria)
            if (!$imagen) {
                $imagen = $galeria->first(function ($img) use ($producto) {
                    $tags = $img->tags ?? [];
                    foreach ($tags as $tag) {
                        if (mb_strtolower($tag) === mb_strtolower($producto->nombre)) {
                            return true;
                        }
                    }
                    return false;
                });
            }

            // 3. Coincidencia parcial por nombre (contiene)
            if (!$imagen) {
                $imagen = $galeria->first(function ($img) use ($nombreLower) {
                    return str_contains(mb_strtolower($img->nombre), $nombreLower)
                        || str_contains($nombreLower, mb_strtolower($img->nombre));
                });
            }

            if ($imagen) {
                $this->line(sprintf(
                    "\n  <fg=green>✓</> Tenant %s | <comment>%s</comment> → %s",
                    $producto->tenant_id,
                    $producto->nombre,
                    $imagen->nombre
                ));

                if (!$dryRun) {
                    $producto->update(['imagen' => $imagen->url]);
                    $imagen->increment('veces_usado');
                }

                $asignados++;
            } else {
                $sinMatch++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(
            ['', 'Cantidad'],
            [
                ['Imágenes asignadas', $asignados],
                ['Sin coincidencia',   $sinMatch],
                ['Total procesados',   $productos->count()],
            ]
        );

        if ($dryRun) {
            $this->warn('DRY-RUN: ningún cambio fue guardado.');
        }

        return self::SUCCESS;
    }
}
