<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = [
            // Bebidas
            'Coca cola',
            'Pepsi',
            'Sprite',
            'Fanta',
            'Cerveza',
            'Vino',
            'Agua',
            'Jugo',
            'Energizante',
            'Gaseosa',

            // Snacks
            'Papas',
            'Galletas',
            'Chocolate',
            'Caramelos',
            'Gomitas',
            'Chicles',

            // Lácteos
            'Leche',
            'Yogurt',
            'Queso',

            // Otros
            'Pan',
            'Arroz',
            'Aceite',
            'Azúcar',
            'Sal',
            'Harina',
            'Enlatado',
            'Frozen',
            'Orgánico',
            'Sin azúcar',
            'Light',
            'Zero',
        ];

        foreach ($tags as $tagName) {
            Tag::findOrCreateByName($tagName);
        }

        $this->command->info('Tags básicos creados exitosamente!');
    }
}
