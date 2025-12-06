<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Institution;

class ActivateInstitution extends Command
{
    protected $signature = 'institution:activate {guid}';
    protected $description = 'Activate an institution by BCeID business GUID';

    public function handle()
    {
        $guid = $this->argument('guid');

        $institution = Institution::where('bceid_business_guid', $guid)->first();

        if (!$institution) {
            $this->error("Institution not found with GUID: {$guid}");
            return 1;
        }

        $this->info("Institution Found: {$institution->name}");
        $this->info("Current Status: " . ($institution->is_active ? 'Active' : 'Inactive'));

        if (!$institution->is_active) {
            $institution->is_active = true;
            $institution->save();
            $this->info("✓ Institution activated successfully!");
        } else {
            $this->info("✓ Institution is already active.");
        }

        return 0;
    }
}