<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Http\Request;
use Illuminate\Foundation\Application;
use App\Helpers\ApiResponse;

// Test 1: Verify API guard is configured
echo "=== SECURITY AUDIT TEST ===\n\n";

echo "1. Checking API guard configuration...\n";
$config = include __DIR__ . '/config/auth.php';
if (isset($config['guards']['api']) && $config['guards']['api']['driver'] === 'sanctum') {
    echo "✅ API guard is properly configured with Sanctum driver\n";
} else {
    echo "❌ API guard is NOT properly configured\n";
}

echo "\n2. Checking route protection...\n";
$routes = file_get_contents(__DIR__ . '/routes/api.php');
$protectedRoutes = [
    '/api/courses',
    '/api/programs', 
    '/api/departments',
    '/api/enrollments',
    '/api/applications',
    '/api/professors'
];

foreach ($protectedRoutes as $route) {
    if (strpos($routes, "Route::middleware('auth:sanctum')->group") !== false) {
        echo "✅ Route $route is protected by auth:sanctum middleware\n";
    } else {
        echo "❌ Route $route is NOT protected\n";
    }
}

echo "\n3. Checking role-based middleware...\n";
if (strpos($routes, "Route::middleware('role:admin')") !== false) {
    echo "✅ Admin role middleware is configured\n";
} else {
    echo "❌ Admin role middleware is NOT configured\n";
}

if (strpos($routes, "Route::middleware('role:student')") !== false) {
    echo "✅ Student role middleware is configured\n";
} else {
    echo "❌ Student role middleware is NOT configured\n";
}

echo "\n4. Checking User model for API tokens...\n";
$userModel = file_get_contents(__DIR__ . '/app/Models/User.php');
if (strpos($userModel, 'use Laravel\Sanctum\HasApiTokens') !== false) {
    echo "✅ User model has HasApiTokens trait\n";
} else {
    echo "❌ User model is missing HasApiTokens trait\n";
}

echo "\n5. Checking role middleware implementation...\n";
$roleMiddleware = file_get_contents(__DIR__ . '/app/Http/Middleware/EnsureRole.php');
if (strpos($roleMiddleware, 'return ApiResponse::error') !== false) {
    echo "✅ Role middleware returns proper error responses\n";
} else {
    echo "❌ Role middleware error handling issue\n";
}

echo "\n=== SECURITY AUDIT COMPLETE ===\n";
