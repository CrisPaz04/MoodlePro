<?php

require 'vendor/autoload.php';

use Aws\S3\S3Client;
use Illuminate\Support\Facades\Storage;

// Cargar Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Test Detallado de S3 ===\n\n";

try {
    // Crear cliente S3 directamente
    $s3Client = new S3Client([
        'version' => 'latest',
        'region'  => env('AWS_DEFAULT_REGION'),
        'credentials' => [
            'key'    => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
        ],
    ]);
    
    $bucket = env('AWS_BUCKET');
    $testKey = 'test/test-file-' . time() . '.txt';
    $testContent = 'Test content from MoodlePro - ' . date('Y-m-d H:i:s');
    
    echo "1. Probando putObject directo con AWS SDK...\n";
    try {
        $result = $s3Client->putObject([
            'Bucket' => $bucket,
            'Key'    => $testKey,
            'Body'   => $testContent,
            // Removemos el ACL por ahora
        ]);
        
        echo "✅ putObject exitoso!\n";
        echo "   URL: " . $result['ObjectURL'] . "\n";
        
        // Verificar que existe
        $exists = $s3Client->doesObjectExist($bucket, $testKey);
        echo "   Existe: " . ($exists ? 'Sí' : 'No') . "\n";
        
        // Eliminar
        $s3Client->deleteObject([
            'Bucket' => $bucket,
            'Key'    => $testKey,
        ]);
        echo "   Eliminado: Sí\n\n";
        
    } catch (\Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n\n";
    }
    
    echo "2. Probando con Laravel Storage (método writeStream)...\n";
    try {
        $disk = Storage::disk('moodlepro');
        $testPath = 'test/laravel-test-' . time() . '.txt';
        
        // Usar writeStream en lugar de put
        $stream = fopen('data://text/plain,' . $testContent, 'r');
        $result = $disk->writeStream($testPath, $stream, [
            'visibility' => 'public',
        ]);
        
        if ($result) {
            echo "✅ writeStream exitoso!\n";
            echo "   URL: " . $disk->url($testPath) . "\n";
            
            // Verificar
            $exists = $disk->exists($testPath);
            echo "   Existe: " . ($exists ? 'Sí' : 'No') . "\n";
            
            // Eliminar
            $disk->delete($testPath);
            echo "   Eliminado: Sí\n\n";
        } else {
            echo "❌ writeStream falló\n\n";
        }
        
    } catch (\Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n\n";
    }
    
    echo "3. Verificando configuración del bucket...\n";
    try {
        // Obtener ACL del bucket
        $acl = $s3Client->getBucketAcl(['Bucket' => $bucket]);
        echo "   Owner: " . $acl['Owner']['DisplayName'] . "\n";
        
        // Obtener ubicación
        $location = $s3Client->getBucketLocation(['Bucket' => $bucket]);
        echo "   Región: " . ($location['LocationConstraint'] ?: 'us-east-1') . "\n";
        
        // Verificar CORS
        try {
            $cors = $s3Client->getBucketCors(['Bucket' => $bucket]);
            echo "   CORS configurado: Sí\n";
        } catch (\Exception $e) {
            echo "   CORS configurado: No\n";
        }
        
    } catch (\Exception $e) {
        echo "❌ Error al obtener configuración: " . $e->getMessage() . "\n";
    }
    
    echo "\n4. Probando subida de archivo real...\n";
    try {
        // Crear archivo temporal
        $tempFile = tempnam(sys_get_temp_dir(), 'moodlepro');
        file_put_contents($tempFile, $testContent);
        
        $uploadPath = 'resources/' . date('Y/m') . '/test-' . uniqid() . '.txt';
        
        // Subir usando putFileAs
        $result = $disk->putFileAs(
            dirname($uploadPath),
            new \Illuminate\Http\File($tempFile),
            basename($uploadPath),
            'public'
        );
        
        if ($result) {
            echo "✅ Subida de archivo exitosa!\n";
            echo "   Path: " . $result . "\n";
            echo "   URL: " . $disk->url($result) . "\n";
            
            // No eliminar para que puedas verificar en S3
            echo "   ⚠️  Archivo dejado en S3 para verificación\n";
        } else {
            echo "❌ Subida de archivo falló\n";
        }
        
        unlink($tempFile);
        
    } catch (\Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error general: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}