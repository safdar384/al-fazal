<?php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$routes = app('router')->getRoutes();
$named = [];
$missing = [];
$refs = [];

foreach ($routes as $route) {
    $action = $route->getAction();
    $name = $route->getName();
    if ($name) {
        $named[] = $name;
    }
    $uses = $action['controller'] ?? null;
    if (!$uses || !is_string($uses)) {
        continue;
    }
    if (preg_match('/^(.+)@(.+)$/', $uses, $m)) {
        $class = $m[1];
        $method = $m[2];
        $label = $name ?: '(unnamed)';
        $refs[] = [$label, $class, $method, implode('|', $route->methods()), $route->uri()];
        if (!class_exists($class)) {
            $missing[] = "CLASS MISSING: {$class}::{$method} (route: {$label} / {$route->uri()})";
        } elseif (!method_exists($class, $method)) {
            $missing[] = "METHOD MISSING: {$class}::{$method} (route: {$label} / {$route->uri()})";
        }
    }
}

$namedUnique = array_values(array_unique($named));
sort($namedUnique);

echo "=== NAMED ROUTES (" . count($namedUnique) . ") ===\n";
foreach ($namedUnique as $n) {
    echo $n . "\n";
}

echo "\n=== MISSING CONTROLLERS/METHODS (" . count($missing) . ") ===\n";
foreach ($missing as $m) {
    echo $m . "\n";
}

file_put_contents(__DIR__ . '/_named_routes.txt', implode("\n", $namedUnique));
file_put_contents(__DIR__ . '/_missing_controllers.txt', implode("\n", $missing));
echo "\nWrote _named_routes.txt and _missing_controllers.txt\n";
