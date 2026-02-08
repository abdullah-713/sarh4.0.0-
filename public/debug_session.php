<?php
/**
 * TEMPORARY DEBUG SCRIPT — DELETE AFTER FIXING 419
 * Tests the session/CSRF flow from the server's perspective.
 */

// Bootstrap Laravel
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

header('Content-Type: text/plain; charset=utf-8');

echo "=== SARH Session Debug ===\n\n";

// 1. Environment
echo "APP_URL: " . config('app.url') . "\n";
echo "APP_ENV: " . config('app.env') . "\n";

// 2. Session Config
echo "\n--- Session Config ---\n";
echo "driver: " . config('session.driver') . "\n";
echo "encrypt: " . var_export(config('session.encrypt'), true) . "\n";
echo "domain: " . var_export(config('session.domain'), true) . "\n";
echo "secure: " . var_export(config('session.secure'), true) . "\n";
echo "same_site: " . config('session.same_site') . "\n";
echo "cookie_name: " . config('session.cookie') . "\n";
echo "path: " . config('session.path') . "\n";
echo "lifetime: " . config('session.lifetime') . "\n";
echo "http_only: " . var_export(config('session.http_only'), true) . "\n";

// 3. Request info
echo "\n--- Request Info ---\n";
echo "HTTPS: " . (isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : 'NOT SET') . "\n";
echo "SERVER_PORT: " . ($_SERVER['SERVER_PORT'] ?? 'NOT SET') . "\n";
echo "HTTP_X_FORWARDED_PROTO: " . ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? 'NOT SET') . "\n";
echo "HTTP_X_FORWARDED_FOR: " . ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? 'NOT SET') . "\n";
echo "REQUEST_SCHEME: " . ($_SERVER['REQUEST_SCHEME'] ?? 'NOT SET') . "\n";
echo "REMOTE_ADDR: " . ($_SERVER['REMOTE_ADDR'] ?? 'NOT SET') . "\n";
echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'NOT SET') . "\n";

// 4. Check if request is secure (Laravel's perspective)
$request = Illuminate\Http\Request::capture();
echo "\n--- Laravel Request ---\n";
echo "isSecure(): " . var_export($request->isSecure(), true) . "\n";
echo "getScheme(): " . $request->getScheme() . "\n";
echo "getHost(): " . $request->getHost() . "\n";
echo "url(): " . $request->url() . "\n";

// 5. Session files
$sessionPath = storage_path('framework/sessions');
$files = glob($sessionPath . '/*');
echo "\n--- Session Files ---\n";
echo "Path: " . $sessionPath . "\n";
echo "Count: " . count($files) . "\n";
echo "Writable: " . var_export(is_writable($sessionPath), true) . "\n";

// 6. TrustProxies check
echo "\n--- Middleware ---\n";
$middlewareClasses = [
    \Illuminate\Http\Middleware\TrustProxies::class,
];
foreach ($middlewareClasses as $class) {
    echo $class . ": " . (class_exists($class) ? 'EXISTS' : 'MISSING') . "\n";
}

echo "\n=== END DEBUG ===\n";
