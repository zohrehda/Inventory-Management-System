<?php

namespace App\Tenancy\Database\Factories;

use App\Models\Model;
use App\Tenancy\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Model>
 */
class TenantFactory extends Factory
{
        protected $model = Tenant::class;


    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'      => fake()->company(),
            'subdomain' => fake()->unique()->domainWord(),
        ];
    }
}
