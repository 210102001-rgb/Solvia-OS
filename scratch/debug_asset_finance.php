<?php

require __DIR__ . '/../../../../xampp/htdocs/solvia-nova-os/vendor/autoload.php';
$app = require_once __DIR__ . '/../../../../xampp/htdocs/solvia-nova-os/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Http\Controllers\FinanceController;
use App\Services\FinancialLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

$superAdmin = User::where('email', 'nandaariwahyu07@gmail.com')->first();
Auth::login($superAdmin);

try {
    $controller = new FinanceController(app(FinancialLedgerService::class));
    $view = $controller->assetFinance(new Request());
    echo $view->render();
    echo "SUCCESS!\n";
} catch (\Throwable $e) {
    echo "EXCEPTION: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo $e->getTraceAsString();
}
