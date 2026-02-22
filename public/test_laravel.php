?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Diagnóstico de Laravel</h1>";

try {
    echo "<p>Cargando vendor/autoload.php...</p>";
    require __DIR__.'/../vendor/autoload.php';
    echo "<p style='color:green'>✓ Vendor cargado correctamente</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Error: " . $e->getMessage() . "</p>";
}

try {
    echo "<p>Cargando bootstrap/app.php...</p>";
    $app = require_once __DIR__.'/../bootstrap/app.php';
    echo "<p style='color:green'>✓ App cargada correctamente</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Error: " . $e->getMessage() . "</p>";
}

echo "<h2 style='color:green'>✓ TODO FUNCIONA!</h2>";
