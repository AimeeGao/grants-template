<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Role>
 */
class RoleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Role::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->slug(2),
            'display_name' => $this->faker->words(2, true),
            'description' => $this->faker->sentence(),
            'is_active' => true,
        ];
    }

    /**
     * Create a student role.
     */
    public function student(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => Role::STUDENT,
            'display_name' => 'Student',
            'description' => 'Access for students (BC Services Card)',
            'is_active' => true,
        ]);
    }

    /**
     * Create a ministry admin role.
     */
    public function ministryAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => Role::MINISTRY_ADMIN,
            'display_name' => 'Ministry Administrator',
            'description' => 'Full administrative access for ministry users (IDIR)',
            'is_active' => true,
        ]);
    }

    /**
     * Create a ministry user role.
     */
    public function ministryUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => Role::MINISTRY_USER,
            'display_name' => 'Ministry User',
            'description' => 'Standard access for ministry users (IDIR)',
            'is_active' => true,
        ]);
    }

    /**
     * Create an institution user role.
     */
    public function institutionUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => Role::INSTITUTION_USER,
            'display_name' => 'Institution User',
            'description' => 'Access for institutional users (BCeID)',
            'is_active' => true,
        ]);
    }

    /**
     * Create an institution admin role.
     */
    public function institutionAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => Role::INSTITUTION_ADMIN,
            'display_name' => 'Institution Administrator',
            'description' => 'Administrative access for institutional users (BCeID)',
            'is_active' => true,
        ]);
    }

    /**
     * Create a super admin role.
     */
    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => Role::SUPER_ADMIN,
            'display_name' => 'Super Administrator',
            'description' => 'Full system administrator with all privileges',
            'is_active' => true,
        ]);
    }

    /**
     * Create a security officer role.
     */
    public function securityOfficer(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => Role::SECURITY_OFFICER,
            'display_name' => 'Security Officer',
            'description' => 'Security review and approval access',
            'is_active' => true,
        ]);
    }

    /**
     * Create a privacy officer role.
     */
    public function privacyOfficer(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => Role::PRIVACY_OFFICER,
            'display_name' => 'Privacy Officer',
            'description' => 'Privacy review and approval access',
            'is_active' => true,
        ]);
    }

    /**
     * Create an inactive role.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
