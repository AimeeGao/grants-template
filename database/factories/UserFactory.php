<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'guid' => fake()->uuid(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'identity_provider' => 'testing',
            'is_active' => true,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model should be inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Create a student user with BCSC authentication.
     */
    public function student(): static
    {
        return $this->state(fn (array $attributes) => [
            'bcsc_user_guid' => fake()->uuid(),
            'identity_provider' => 'bcsc',
            'email' => fake()->unique()->email(),
        ])->afterCreating(function (User $user) {
            $studentRole = Role::where('name', Role::STUDENT)->first();
            if ($studentRole) {
                $user->roles()->attach($studentRole);
            }
        });
    }

    /**
     * Create an institution user with BCeID Business authentication.
     */
    public function institutionUser(string $businessGuid = null): static
    {
        return $this->state(fn (array $attributes) => [
            'bceid_username' => fake()->userName(),
            'bceid_user_guid' => fake()->uuid(),
            'bceid_business_guid' => $businessGuid ?? 'TEST-GUID-123',
            'identity_provider' => 'bceid_business',
            'email' => fake()->unique()->companyEmail(),
        ])->afterCreating(function (User $user) {
            $role = Role::where('name', Role::INSTITUTION_USER)->first();
            if ($role) {
                $user->roles()->attach($role);
            }
        });
    }

    /**
     * Create an institution admin with BCeID Business authentication.
     */
    public function institutionAdmin(string $businessGuid = null): static
    {
        return $this->state(fn (array $attributes) => [
            'bceid_username' => fake()->userName(),
            'bceid_user_guid' => fake()->uuid(),
            'bceid_business_guid' => $businessGuid ?? fake()->regexify('[A-Z]{4}-GUID-[0-9]{3}'),
            'identity_provider' => 'bceid',
            'email' => fake()->unique()->companyEmail(),
        ])->afterCreating(function (User $user) {
            $role = Role::where('name', Role::INSTITUTION_ADMIN)->first();
            if ($role) {
                $user->roles()->attach($role);
            }
        });
    }

    /**
     * Create a ministry user with IDIR authentication.
     */
    public function ministryUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'idir_username' => fake()->userName() . '@IDIR',
            'idir_user_guid' => fake()->uuid(),
            'identity_provider' => 'idir',
            'email' => fake()->userName() . '@gov.bc.ca',
        ])->afterCreating(function (User $user) {
            $role = Role::where('name', Role::MINISTRY_USER)->first();
            if ($role) {
                $user->roles()->attach($role);
            }
        });
    }

    /**
     * Create an admin manager with IDIR authentication.
     */
    public function adminManager(): static
    {
        return $this->state(fn (array $attributes) => [
            'idir_username' => fake()->userName() . '@IDIR',
            'idir_user_guid' => fake()->uuid(),
            'identity_provider' => 'idir',
            'email' => fake()->userName() . '@gov.bc.ca',
        ])->afterCreating(function (User $user) {
            $role = Role::where('name', Role::ADMIN_MANAGER)->first();
            if ($role) {
                $user->roles()->attach($role);
            }
        });
    }

    /**
     * Create a super admin with IDIR authentication.
     */
    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'idir_username' => fake()->userName() . '@IDIR',
            'idir_user_guid' => fake()->uuid(),
            'identity_provider' => 'idir',
            'email' => fake()->userName() . '@gov.bc.ca',
        ])->afterCreating(function (User $user) {
            $role = Role::where('name', Role::SUPER_ADMIN)->first();
            if ($role) {
                $user->roles()->attach($role);
            }
        });
    }
}
