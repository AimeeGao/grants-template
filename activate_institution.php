<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Modules\Institution\Models\Institution;

$guid = '8ab44c60-b3d0-492e-8ae5-364a44445b2d';
$institution = Institution::where('bceid_business_guid', $guid)->first();

if ($institution) {
    echo "Institution Found: {$institution->name}\n";
    echo "Current Status: " . ($institution->is_active ? 'Active' : 'Inactive') . "\n\n";

    if (!$institution->is_active) {
        $institution->is_active = true;
        $institution->save();
        echo "✓ Institution activated successfully!\n";
    } else {
        echo "✓ Institution is already active.\n";
    }
} else {
    echo "✗ Institution not found with GUID: {$guid}\n";
}