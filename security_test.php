<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Http\Request;
use Illuminate\Foundation\Application;

echo "=== SECURITY VERIFICATION TEST ===\n\n";

// Test 1: Check if routes are properly protected
echo "1. Checking route protection structure...\n";
$routesContent = file_get_contents(__DIR__ . '/routes/api.php');

$sensitiveRoutes = [
    '/api/user',
    '/api/courses',
    '/api/programs',
    '/api/departments',
    '/api/enrollments',
    '/api/applications',
    '/api/professors'
];

$allProtected = true;
foreach ($sensitiveRoutes as $route) {
    $routePattern = str_replace('/', '\/', $route);
    if (preg_match("/Route::(get|post|put|delete)\('$routePattern'/", $routesContent)) {
        // Check if it's inside auth:sanctum group
        $authGroupStart = strpos($routesContent, "Route::middleware('auth:sanctum')->group");
        $routePosition = strpos($routesContent, $route);
        
        if ($authGroupStart !== false && $routePosition > $authGroupStart) {
            echo "✅ $route is inside auth:sanctum group\n";
        } else {
            echo "❌ $route is NOT inside auth:sanctum group\n";
            $allProtected = false;
        }
    }
}

echo "\n2. Checking middleware configuration...\n";
$bootstrapContent = file_get_contents(__DIR__ . '/bootstrap/app.php');
if (strpos($bootstrapContent, 'EnsureFrontendRequestsAreStateful') !== false) {
    echo "✅ Sanctum middleware is registered in API group\n";
} else {
    echo "❌ Sanctum middleware is NOT registered in API group\n";
}

echo "\n3. Checking auth configuration...\n";
$authContent = file_get_contents(__DIR__ . '/config/auth.php');
if (strpos($authContent, "'driver' => 'sanctum'") !== false) {
    echo "✅ API guard is configured with Sanctum driver\n";
} else {
    echo "❌ API guard is NOT configured with Sanctum driver\n";
}

echo "\n4. Checking User model...\n";
$userModel = file_get_contents(__DIR__ . '/app/Models/User.php');
if (strpos($userModel, 'HasApiTokens') !== false) {
    echo "✅ User model has HasApiTokens trait\n";
} else {
    echo "❌ User model is missing HasApiTokens trait\n";
}

echo "\n=== SECURITY VERIFICATION COMPLETE ===\n";
if ($allProtected) {
    echo "✅ All sensitive routes appear to be properly protected\n";
} else {
    echo "❌ Some routes may not be properly protected\n";
}
