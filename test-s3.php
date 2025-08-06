<?php

require 'vendor/autoload.php';

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;

// Cargar Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Probando conexión a S3...\n";
    
    // Cargar configuración
    $config = config('filesystems.disks.moodlepro');
    
    echo "=== Configuración ===\n";
    echo "Bucket: " . $config['bucket'] . "\n";
    echo "Region: " . $config['region'] . "\n";
    echo "Key: " . substr($config['key'], 0, 10) . "...\n";
    echo "URL: " . $config['url'] . "\n\n";
    
    // Verificar que tengamos las credenciales
    if (empty($config['key']) || empty($config['secret']) || empty($config['bucket'])) {
        echo "❌ Faltan credenciales de AWS en el archivo .env\n";
        echo "Asegúrate de tener estas variables en tu .env:\n";
        echo "AWS_ACCESS_KEY_ID=tu-access-key\n";
        echo "AWS_SECRET_ACCESS_KEY=tu-secret-key\n";
        echo "AWS_DEFAULT_REGION=us-east-2\n";
        echo "AWS_BUCKET=moodlepro-bucket\n";
        exit(1);
    }
    
    // Obtener el disco S3
    $disk = Storage::disk('moodlepro');
    
    // Crear un archivo de prueba
    $testContent = "Prueba de conexión S3 - " . date('Y-m-d H:i:s');
    $testPath = 'test/connection-test.txt';
    
    echo "Subiendo archivo de prueba...\n";
    
    try {
        // Intentar subir directamente
        $result = $disk->put($testPath, $testContent, [
            'visibility' => 'public',
            'ACL' => 'public-read'
        ]);
        
        if ($result) {
            echo "✅ Archivo subido exitosamente\n";
            
            // Obtener URL
            $url = $disk->url($testPath);
            echo "URL del archivo: " . $url . "\n";
            
            // Verificar que existe
            if ($disk->exists($testPath)) {
                echo "✅ El archivo existe en S3\n";
                
                // Leer el contenido
                $content = $disk->get($testPath);
                echo "Contenido: " . $content . "\n";
                
                // Eliminar el archivo de prueba
                $disk->delete($testPath);
                echo "✅ Archivo de prueba eliminado\n";
            }
        } else {
            echo "❌ Error al subir el archivo (resultado falso)\n";
            
            // Intentar con putFileAs para ver si hay más información
            echo "\nVerificando permisos del bucket...\n";
            
            // Intentar listar archivos para verificar permisos de lectura
            try {
                $files = $disk->files('test');
                echo "✅ Puedo listar archivos en el bucket\n";
            } catch (\Exception $e) {
                echo "❌ No puedo listar archivos: " . $e->getMessage() . "\n";
            }
            
            // Crear un archivo temporal para probar
            $tempFile = tempnam(sys_get_temp_dir(), 'test');
            file_put_contents($tempFile, $testContent);
            
            try {
                $uploaded = Storage::disk('moodlepro')->putFile('test', new \Illuminate\Http\File($tempFile), 'public');
                if ($uploaded) {
                    echo "✅ Subida con putFile funcionó: " . $uploaded . "\n";
                    $disk->delete($uploaded);
                } else {
                    echo "❌ putFile también falló\n";
                }
            } catch (\Exception $e) {
                echo "❌ Error con putFile: " . $e->getMessage() . "\n";
            }
            
            unlink($tempFile);
        }
    } catch (\Aws\Exception\AwsException $e) {
        echo "❌ Error AWS: " . $e->getAwsErrorMessage() . "\n";
        echo "Código de error: " . $e->getAwsErrorCode() . "\n";
        echo "Tipo de error: " . $e->getAwsErrorType() . "\n";
        echo "Detalles completos: " . $e->getMessage() . "\n";
    } catch (\Exception $e) {
        echo "❌ Error general: " . $e->getMessage() . "\n";
        echo "Clase de error: " . get_class($e) . "\n";
        
        // Más detalles si es un error de credenciales
        if (strpos($e->getMessage(), 'Could not resolve host') !== false) {
            echo "\n⚠️  Problema de conexión a Internet o DNS\n";
        } elseif (strpos($e->getMessage(), 'InvalidAccessKeyId') !== false) {
            echo "\n⚠️  Las credenciales de AWS son incorrectas\n";
        } elseif (strpos($e->getMessage(), 'SignatureDoesNotMatch') !== false) {
            echo "\n⚠️  La clave secreta de AWS es incorrecta\n";
        } elseif (strpos($e->getMessage(), 'AccessDenied') !== false) {
            echo "\n⚠️  No tienes permisos para acceder al bucket\n";
            echo "Verifica:\n";
            echo "1. Que el usuario IAM tenga la política AmazonS3FullAccess\n";
            echo "2. Que el bucket tenga los permisos públicos correctos\n";
            echo "3. Que el bucket esté en la región us-east-2\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Tipo de error: " . get_class($e) . "\n";
    
    if (strpos($e->getMessage(), 'Could not resolve host') !== false) {
        echo "\n⚠️  Problema de conexión a Internet o DNS\n";
    } elseif (strpos($e->getMessage(), 'InvalidAccessKeyId') !== false) {
        echo "\n⚠️  Las credenciales de AWS son incorrectas\n";
    } elseif (strpos($e->getMessage(), 'SignatureDoesNotMatch') !== false) {
        echo "\n⚠️  La clave secreta de AWS es incorrecta\n";
    } elseif (strpos($e->getMessage(), 'NoSuchBucket') !== false) {
        echo "\n⚠️  El bucket no existe o el nombre es incorrecto\n";
    }
}