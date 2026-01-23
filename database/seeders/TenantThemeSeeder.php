<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TenantThemeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Colores disponibles de la plantilla
        $themes = [
            1 => ['color' => '#884a39', 'number' => 5], // Color 5 - Marrón
            2 => ['color' => '#57375d', 'number' => 2], // Color 2 - Púrpura
            3 => ['color' => '#0766ad', 'number' => 3], // Color 3 - Azul
            4 => ['color' => '#025464', 'number' => 4], // Color 4 - Verde azulado
            5 => ['color' => '#0c356a', 'number' => 6], // Color 6 - Azul oscuro
        ];

        // Asignar colores a los tenants existentes
        $tenants = DB::table('tenants')->get();

        foreach ($tenants as $index => $tenant) {
            $themeIndex = ($index % count($themes)) + 1;
            DB::table('tenants')
                ->where('id', $tenant->id)
                ->update([
                    'theme_color' => $themes[$themeIndex]['color'],
                    'theme_number' => $themes[$themeIndex]['number']
                ]);
        }
    }
}
