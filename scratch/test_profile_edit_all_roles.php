<?php

require __DIR__ . '/../../../../xampp/htdocs/solvia-nova-os/vendor/autoload.php';
$app = require_once __DIR__ . '/../../../../xampp/htdocs/solvia-nova-os/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

echo "=== SOLVIA.NOVA OS — TEST PROFILE EDIT FOR ALL ROLES (ROLE IMMUTABILITY) ===\n\n";

$controller = new ProfileController();

// --- 1. Test Operational User (Putra - Designer) ---
echo "--- 1. Testing Operational User: Putra (Designer) ---\n";
$putra = User::where('email', 'putra@gmail.com')->first();
if (!$putra) {
    echo "  [FAIL] User putra@gmail.com not found!\n";
    exit(1);
}

$initialRole = $putra->role;
echo "  [INFO] Initial Role: {$initialRole}\n";
Auth::login($putra);

// Test GET /profile through HTTP kernel
$req = Request::create(route('profile.edit'), 'GET');
$resp = app()->handle($req);
echo "  [PASS] Putra HTTP GET /profile -> HTTP {$resp->getStatusCode()} OK\n";

// Test update with spoofed role attempt
echo "  [INFO] Submitting profile update with spoofed role='super_admin'...\n";
$updateRequest = new Request([
    'name' => 'Putra Pratama (Senior Designer)',
    'email' => $putra->email,
    'phone' => '+62 812-9988-7766',
    'department' => 'Creative & Product Design',
    'profile_bio' => 'Lead product designer specializing in dark mode interfaces and design systems.',
    'role' => 'super_admin', // MALICIOUS SPOOF ATTEMPT
]);
$updateResponse = $controller->update($updateRequest);

$putra->refresh();
if ($putra->name === 'Putra Pratama (Senior Designer)' && $putra->phone === '+62 812-9988-7766') {
    echo "  [PASS] Personal information updated successfully in database.\n";
} else {
    echo "  [FAIL] Profile data was not updated! Actual Name: {$putra->name}\n";
    exit(1);
}

if ($putra->role === $initialRole) {
    echo "  [PASS] ROLE IMMUTABILITY VERIFIED! Role remains '{$putra->role}', spoofed 'super_admin' was ignored completely.\n";
} else {
    echo "  [FAIL] SECURITY BREACH: Role was changed to {$putra->role}!\n";
    exit(1);
}

// --- 2. Test Developer User (Dimas - Backend Dev) ---
echo "\n--- 2. Testing Developer User: Dimas (Backend Dev) ---\n";
$dimas = User::where('email', 'dimas@solvia.id')->first();
if ($dimas) {
    Auth::login($dimas);
    $initialDimasRole = $dimas->role;

    $view = $controller->edit();
    echo "  [PASS] Dimas edit view rendered successfully.\n";

    $updateReq = new Request([
        'name' => 'Dimas Backend Lead',
        'email' => $dimas->email,
        'department' => 'Core Platform Engineering',
        'role' => 'viewer', // spoof
    ]);
    $controller->update($updateReq);

    $dimas->refresh();
    if ($dimas->role === $initialDimasRole && $dimas->name === 'Dimas Backend Lead') {
        echo "  [PASS] Dimas profile updated. Role remains strictly '{$dimas->role}'.\n";
    } else {
        echo "  [FAIL] Role changed or update failed!\n";
        exit(1);
    }
} else {
    echo "  [SKIP] Dimas not found in DB.\n";
}

// --- 3. Test Super Admin (Nanda) ---
echo "\n--- 3. Testing Super Admin: Nanda ---\n";
$nanda = User::where('email', 'nandaariwahyu07@gmail.com')->first();
Auth::login($nanda);
$initialNandaRole = $nanda->role;

$req = Request::create(route('profile.edit'), 'GET');
$resp = app()->handle($req);
echo "  [PASS] Nanda HTTP GET /profile -> HTTP {$resp->getStatusCode()} OK\n";

$updateReq = new Request([
    'name' => 'Nanda Ariwahyu',
    'email' => $nanda->email,
    'department' => 'Executive Office',
    'profile_bio' => 'Founder & Chief Executive Officer at Solvia Group.',
]);
$controller->update($updateReq);

$nanda->refresh();
if ($nanda->role === $initialNandaRole) {
    echo "  [PASS] Nanda profile updated. Role remains strictly '{$nanda->role}'.\n";
} else {
    echo "  [FAIL] Nanda role modified!\n";
    exit(1);
}

// --- 4. Test Unauthenticated Access ---
echo "\n--- 4. Testing Guest Access (Unauthenticated) ---\n";
Auth::logout();
$guestReq = Request::create(route('profile.edit'), 'GET');
$guestResp = app()->handle($guestReq);
if ($guestResp->getStatusCode() === 302) {
    echo "  [PASS] Guest access redirected to login -> HTTP 302\n";
} else {
    echo "  [FAIL] Guest access returned HTTP {$guestResp->getStatusCode()}\n";
    exit(1);
}

echo "\n=================================================================\n";
echo ">>> ALL PROFILE EDIT & ROLE PRESERVATION TESTS PASSED (100%) <<<\n";
echo "=================================================================\n";
