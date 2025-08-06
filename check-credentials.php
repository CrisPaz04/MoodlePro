<?php

require 'vendor/autoload.php';

// Cargar Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Verificación de Credenciales AWS ===\n\n";

// Obtener credenciales del .env
$key = env('AWS_ACCESS_KEY_ID');
$secret = env('AWS_SECRET_ACCESS_KEY');
$region = env('AWS_DEFAULT_REGION');
$bucket = env('AWS_BUCKET');

echo "Access Key ID: " . $key . "\n";
echo "Longitud Access Key: " . strlen($key) . " (debe ser 20)\n";
echo "Secret Key (primeros 10 chars): " . substr($secret, 0, 10) . "...\n";
echo "Longitud Secret Key: " . strlen($secret) . " (debe ser 40)\n";
echo "Region: " . $region . "\n";
echo "Bucket: " . $bucket . "\n\n";

// Verificar caracteres especiales
echo "=== Verificación de caracteres ===\n";
if ($key !== trim($key)) {
    echo "⚠️  La Access Key tiene espacios en blanco\n";
}
if ($secret !== trim($secret)) {
    echo "⚠️  La Secret Key tiene espacios en blanco\n";
}

// Mostrar caracteres especiales en la secret key
echo "Caracteres especiales en Secret Key: ";
preg_match_all('/[^a-zA-Z0-9]/', $secret, $matches);
if (empty($matches[0])) {
    echo "Ninguno\n";
} else {
    echo implode(', ', array_unique($matches[0])) . "\n";
}

echo "\n=== Prueba directa con AWS SDK ===\n";

try {
    $s3Client = new \Aws\S3\S3Client([
        'version' => 'latest',
        'region'  => $region,
        'credentials' => [
            'key'    => trim($key),
            'secret' => trim($secret),
        ],
    ]);
    
    echo "Cliente S3 creado exitosamente\n";
    
    // Intentar listar buckets
    echo "Intentando listar buckets...\n";
    $result = $s3Client->listBuckets();
    
    echo "✅ Conexión exitosa! Buckets encontrados:\n";
    foreach ($result['Buckets'] as $bucket) {
        echo "  - " . $bucket['Name'] . "\n";
    }
    
} catch (\Aws\Exception\AwsException $e) {
    echo "❌ Error AWS: " . $e->getAwsErrorMessage() . "\n";
    echo "Código: " . $e->getAwsErrorCode() . "\n";
    
    if ($e->getAwsErrorCode() === 'InvalidAccessKeyId') {
        echo "\n⚠️  La Access Key ID no es válida\n";
    } elseif ($e->getAwsErrorCode() === 'SignatureDoesNotMatch') {
        echo "\n⚠️  La Secret Access Key no es válida\n";
        echo "Verifica que hayas copiado correctamente la clave secreta\n";
    }
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}