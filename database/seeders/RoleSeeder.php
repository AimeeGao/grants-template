<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            
            [
                'name' => Role::SUPER_ADMIN,
                'display_name' => 'Super Administrator',
                'description' => 'Full system administrator with all privileges',
                'is_active' => true,
            ],
            [
                'name' => Role::MINISTRY_ADMIN,
                'display_name' => 'Ministry Administrator',
                'description' => 'Full administrative access for ministry users (IDIR)',
                'is_active' => true,
            ],
            [
                'name' => Role::MINISTRY_USER,
                'display_name' => 'Ministry User',
                'description' => 'Standard access for ministry users (IDIR)',
                'is_active' => true,
            ],
            [
                'name' => Role::INSTITUTION_USER,
                'display_name' => 'Institution User',
                'description' => 'Access for institutional users (BCeID)',
                'is_active' => true,
            ],
            [
                'name' => Role::INSTITUTION_ADMIN,
                'display_name' => 'Institution Administrator',
                'description' => 'Administrative access for institutional users (BCeID)',
                'is_active' => true,
            ],
            [
                'name' => Role::STUDENT,
                'display_name' => 'Student',
                'description' => 'Access for students (BC Services Card)',
                'is_active' => true,
            ],
            [
                'name' => Role::ADMIN_MANAGER,
                'display_name' => 'Admin Manager',
                'description' => 'Administrative manager with comprehensive application and institution management permissions. Can manage institutions, applications, and has all Application Manager capabilities.',
                'is_active' => true,
            ],
            [
                'name' => Role::APPLICATION_MANAGER,
                'display_name' => 'Application Manager',
                'description' => 'Manages applications and workflows on admin portal',
                'is_active' => true,
            ],
            [
                'name' => Role::SECURITY_OFFICER,
                'display_name' => 'Security Officer',
                'description' => 'Security oversight and application approvals on admin portal',
                'is_active' => true,
            ],
            [
                'name' => Role::PRIVACY_OFFICER,
                'display_name' => 'Privacy Officer',
                'description' => 'Privacy compliance and data protection on admin portal',
                'is_active' => true,
            ],
            [
                'name' => Role::ADMIN_GUEST,
                'display_name' => 'Admin Guest',
                'description' => 'Newly created IDIR user pending approval on admin portal',
                'is_active' => true,
            ],
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate(
                ['name' => $roleData['name']],
                $roleData
            );
        }

        $this->command->info('Roles seeded successfully.');
    }
}
