<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- VERIFYING & RESETTING PASSWORDS ---\n";

$users = [
    'admin@jemaah.com',
    'admin.desa@test.com',
    'admin.kelompok@test.com'
];

foreach ($users as $email) {
    $user = User::where('email', $email)->first();
    if ($user) {
        $user->password = Hash::make('password');
        $user->save();
        echo "✅ User {$email} found. Password reset to 'password'.\n";
        echo "   Role: {$user->role}\n";
        echo "   Scope: Desa ID={$user->desa_id}, Kelompok ID={$user->kelompok_id}\n";
    } else {
        echo "❌ User {$email} NOT FOUND!\n";
    }
}

echo "--- DONE ---\n";
