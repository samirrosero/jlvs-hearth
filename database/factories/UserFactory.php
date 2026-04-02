<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    /**
     * Define el estado por defecto del usuario de prueba.
     * Nota: empresa_id y rol_id deben existir antes de usar esta factory.
     */
    public function definition(): array
    {
        return [
            'empresa_id'        => 1,
            'rol_id'            => 1,
            'nombre'            => fake('es_CO')->name(),
            'email'             => fake()->unique()->safeEmail(),
            'identificacion'    => fake()->unique()->numerify('##########'),
            'email_verified_at' => now(),
            'password'          => static::$password ??= Hash::make('password'),
            'remember_token'    => Str::random(10),
        ];
    }

    /**
     * Estado: usuario sin correo verificado.
     */
    public function sinVerificar(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
