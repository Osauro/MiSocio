<?php

namespace Database\Factories;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cliente>
 */
class ClienteFactory extends Factory
{
    protected $model = Cliente::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nombres = [
            'Juan', 'María', 'Pedro', 'Ana', 'Carlos', 'Lucía', 'José', 'Carmen',
            'Luis', 'Rosa', 'Miguel', 'Elena', 'Jorge', 'Patricia', 'Fernando',
            'Isabel', 'Roberto', 'Sofía', 'Ricardo', 'Laura', 'Diego', 'Claudia',
            'Andrés', 'Gabriela', 'Raúl', 'Daniela', 'Alberto', 'Valentina',
            'Javier', 'Camila', 'Ernesto', 'Natalia', 'Mario', 'Andrea'
        ];

        $apellidos = [
            'García', 'Rodríguez', 'Martínez', 'López', 'González', 'Pérez',
            'Sánchez', 'Ramírez', 'Torres', 'Flores', 'Rivera', 'Gómez',
            'Díaz', 'Cruz', 'Morales', 'Reyes', 'Gutiérrez', 'Ortiz',
            'Chávez', 'Ruiz', 'Quispe', 'Mamani', 'Condori', 'Apaza'
        ];

        $calles = [
            'Av. 6 de Agosto', 'Calle Comercio', 'Av. Camacho', 'Calle Sagárnaga',
            'Av. Buenos Aires', 'Calle Potosí', 'Av. Arce', 'Calle Loayza',
            'Av. 16 de Julio', 'Calle Jaén', 'Av. Illimani', 'Calle Murillo',
            'Av. Ballivián', 'Calle Linares', 'Av. Villarroel', 'Calle Indaburo'
        ];

        $zonas = [
            'Sopocachi', 'Miraflores', 'San Pedro', 'Centro', 'Calacoto',
            'Achumani', 'Villa Fátima', 'El Alto', 'Obrajes', 'Irpavi',
            'San Miguel', 'Zona Sur', 'Cota Cota', 'Seguencoma'
        ];

        return [
            'nombre' => fake()->randomElement($nombres) . ' ' . fake()->randomElement($apellidos) . ' ' . fake()->randomElement($apellidos),
            'celular' => '7' . fake()->numberBetween(1000000, 9999999),
            'direccion' => fake()->randomElement($calles) . ' #' . fake()->numberBetween(100, 9999) . ', ' . fake()->randomElement($zonas),
            'nit' => fake()->optional(0.7)->numerify('########'),
        ];
    }

    /**
     * Cliente sin NIT.
     */
    public function sinNit(): static
    {
        return $this->state(fn (array $attributes) => [
            'nit' => null,
        ]);
    }

    /**
     * Cliente sin nombre (SN) para ventas rápidas.
     */
    public function sinNombre(): static
    {
        return $this->state(fn (array $attributes) => [
            'nombre' => 'SN',
            'celular' => 'SN',
            'direccion' => 'SN',
            'nit' => 'SN',
        ]);
    }
}
