<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

// Find BCeID users
$bceidUsers = User::where('identity_provider', 'bceid')->get();

if ($bceidUsers->isEmpty()) {
    echo "No BCeID users found.\n";
    exit;
}

echo "BCeID Users:\n";
echo "============\n\n";

foreach ($bceidUsers as $user) {
    echo "ID: {$user->id}\n";
    echo "Name: {$user->first_name} {$user->last_name}\n";
    echo "Email: {$user->email}\n";
    echo "BCeID Business GUID: {$user->bceid_business_guid}\n";
    echo "Organization: " . ($user->organization ?? 'N/A') . "\n";
    echo "---\n\n";
}