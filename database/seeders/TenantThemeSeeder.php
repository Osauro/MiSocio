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
        // Los theme_number ya están asignados en TenantSeeder
        // Este seeder ya no es necesario para theme_color
        // Solo actualizamos theme_number si algún tenant no lo tiene

        $themes = [1, 2, 3, 4, 5, 6];

        $tenants = DB::table('tenants')->get();

        foreach ($tenants as $index => $tenant) {
            if (!$tenant->theme_number) {
                $themeNumber = $themes[$index % count($themes)];
                DB::table('tenants')
                    ->where('id', $tenant->id)
                    ->update([
                        'theme_number' => $themeNumber
                    ]);
            }
        }
    }
}
