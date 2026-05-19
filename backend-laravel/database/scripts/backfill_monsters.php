<?php


/**
 * Capa Laravel: backfill monsters.
 * Comentaris: agents/backend/AgentLaravel.md
 */

//================================ NAMESPACES / IMPORTS ============

use App\Models\User;
use Carbon\Carbon;

$types = ['VV', 'VR', 'VL', 'VA'];
$users = User::whereNull('monstre_tipus')->get();

foreach ($users as $user) {
    $user->monstre_tipus = $types[array_rand($types)];
    $user->data_naixement_monstre = Carbon::now();
    $user->save();
    echo "Assigned {$user->monstre_tipus} to {$user->nom}\n";
}

echo "Total updated: " . count($users) . "\n";
