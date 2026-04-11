<?php

namespace App\Core;

use Cloudinary\Api\Upload\UploadApi;
use Cloudinary\Configuration\Configuration;
use Throwable;

class CloudinaryStorage
{
    private bool $enabled = false;
    private string $localUploadDir = '';

    public function __construct()
    {
        $this->localUploadDir = dirname(__DIR__, 2) . '/public/uploads';
        
        $config = require __DIR__ . '/../../config/cloudinary.php';

        $cloudName = trim((string) ($config['cloud_name'] ?? ''));
        $apiKey = trim((string) ($config['api_key'] ?? ''));
        $apiSecret = trim((string) ($config['api_secret'] ?? ''));

        if ($cloudName === '' || $apiKey === '' || $apiSecret === '') {
            return;
        }

        Configuration::instance([
            'cloud' => [
                'cloud_name' => $cloudName,
                'api_key' => $apiKey,
                'api_secret' => $apiSecret,
            ],
            'url' => [
                'secure' => true,
            ],
        ]);

        $this->enabled = true;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function uploadImage(string $tmpFilePath, string $folder): ?string
    {
        if ($this->enabled) {
            $cloudinaryUrl = $this->uploadToCloudinary($tmpFilePath, $folder);
            if ($cloudinaryUrl !== null) {
                return $cloudinaryUrl;
            }
        }

        return $this->saveToLocalUploads($tmpFilePath, $folder);
    }

    private function uploadToCloudinary(string $tmpFilePath, string $folder): ?string
    {
        try {
            $result = (new UploadApi())->upload($tmpFilePath, [
                'folder' => trim($folder, '/'),
                'resource_type' => 'image',
            ]);

            return $result['secure_url'] ?? null;
        } catch (Throwable $e) {
            return null;
        }
    }

    private function saveToLocalUploads(string $tmpFilePath, string $folder): ?string
    {
        if (!is_file($tmpFilePath)) {
            return null;
        }

        $uploadDir = $this->localUploadDir;
        if ($folder !== '') {
            $uploadDir .= '/' . trim((string) $folder, '/');
        }

        if (!is_dir($uploadDir)) {
            if (@mkdir($uploadDir, 0755, true) === false) {
                // Intenta crear con permisos más permisivos
                @chmod($uploadDir, 0777);
                if (!is_dir($uploadDir)) {
                    return null;
                }
            }
        }

        // Asegurar permisos de escritura
        @chmod($uploadDir, 0777);

        $extension = $this->getExtensionFromMimeType($tmpFilePath);
        if ($extension === null) {
            return null;
        }

        $filename = uniqid('img_', true) . '.' . $extension;
        $targetPath = $uploadDir . '/' . $filename;

        if (@copy($tmpFilePath, $targetPath) === false) {
            return null;
        }

        @chmod($targetPath, 0644);

        $folderPath = $folder !== '' ? trim((string) $folder, '/') . '/' : '';
        return '/uploads/' . $folderPath . $filename;
    }

    private function getExtensionFromMimeType(string $filePath): ?string
    {
        $mimeType = mime_content_type($filePath);
        
        $mimeToExt = [
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/x-windows-bmp' => 'bmp',
            'image/bmp' => 'bmp',
            'image/tiff' => 'tiff',
            'image/x-tiff' => 'tiff',
            'image/svg+xml' => 'svg',
        ];

        if (isset($mimeToExt[$mimeType])) {
            return $mimeToExt[$mimeType];
        }

        if (strpos($mimeType, 'image/') === 0) {
            $ext = str_replace('image/', '', $mimeType);
            $ext = str_replace('x-', '', $ext);
            return $ext;
        }

        return null;
    }

    public function deleteByUrl(string $url): bool
    {
        if (!$this->enabled) {
            return $this->deleteLocalUpload($url);
        }

        $publicId = $this->extractPublicIdFromUrl($url);
        if ($publicId === null) {
            return false;
        }

        try {
            $result = (new UploadApi())->destroy($publicId, [
                'resource_type' => 'image',
                'invalidate' => true,
            ]);

            return ($result['result'] ?? null) === 'ok';
        } catch (Throwable $e) {
            return false;
        }
    }

    private function deleteLocalUpload(string $url): bool
    {
        if (strpos($url, '/uploads/') === false) {
            return false;
        }

        $relativePath = parse_url($url, PHP_URL_PATH);
        if ($relativePath === null) {
            return false;
        }

        $filePath = dirname(__DIR__, 2) . '/public' . $relativePath;
        if (is_file($filePath) && @unlink($filePath) === true) {
            return true;
        }
        return false;
    }

    private function extractPublicIdFromUrl(string $url): ?string
    {
        $path = parse_url($url, PHP_URL_PATH);
        if (!$path) {
            return null;
        }

        $normalizedPath = ltrim((string) $path, '/');
        $uploadSegment = '/upload/';
        $uploadPosition = strpos('/' . $normalizedPath, $uploadSegment);

        if ($uploadPosition === false) {
            return null;
        }

        $afterUpload = substr('/' . $normalizedPath, $uploadPosition + strlen($uploadSegment));
        $afterUpload = ltrim($afterUpload, '/');

        if (preg_match('#^v\d+/#', $afterUpload) === 1) {
            $afterUpload = preg_replace('#^v\d+/#', '', $afterUpload);
        } elseif (preg_match('#/v\d+/#', $afterUpload, $matches, PREG_OFFSET_CAPTURE) === 1) {
            $offset = $matches[0][1] + strlen($matches[0][0]);
            $afterUpload = substr($afterUpload, $offset);
        }

        $afterUpload = urldecode($afterUpload);
        $publicId = preg_replace('/\.[^.]+$/', '', $afterUpload);

        $publicId = trim((string) $publicId, '/');
        return $publicId !== '' ? $publicId : null;
    }
}
