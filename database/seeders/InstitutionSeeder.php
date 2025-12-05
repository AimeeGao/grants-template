<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Institution\Models\Institution;
use Illuminate\Support\Str;

class InstitutionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the bceid_business_guid from your logged-in user
        // You should replace this with the actual GUID from your user record
        $bceidGuid = '8ab44c60-b3d0-492e-8ae5-364a44445b2d';

        // Check if institution already exists
        $existing = Institution::where('bceid_business_guid', $bceidGuid)->first();

        if ($existing) {
            $this->command->info("Institution already exists: {$existing->name}");
            return;
        }

        // Create a new institution matching the actual table structure
        Institution::create([
            'guid' => str_replace('-', '', Str::uuid()), // 32 char without dashes
            'bceid_business_guid' => $bceidGuid,
            'name' => 'COLLEGE A',
            'name_code' => 'COLLA',
            'legal_name' => 'COLLEGE A Corporation',
            'dli' => null,
            'size' => 'medium',
            'category' => 'public',
            'economic_region' => 'Victoria',

            // Contact information
            'primary_contact' => 'Admin',
            'primary_email' => 'admin@collegea.ca',

            // Address
            'address1' => '123 University Way',
            'address2' => null,
            'city' => 'Victoria',
            'province' => 'BC',
            'postal_code' => 'V8W2Y2',

            // Status
            'active_status' => true,

            // Timestamps
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info('✓ Institution created successfully!');
        $this->command->info('Name: Test University');
        $this->command->info('BCeID GUID: ' . $bceidGuid);
        $this->command->info('Status: Active');
    }
}