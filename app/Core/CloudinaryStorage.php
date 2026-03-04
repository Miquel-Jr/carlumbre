<?php

namespace App\Core;

use Cloudinary\Api\Upload\UploadApi;
use Cloudinary\Configuration\Configuration;
use Throwable;

class CloudinaryStorage
{
    private bool $enabled = false;

    public function __construct()
    {
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
        if (!$this->enabled) {
            return null;
        }

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

    public function deleteByUrl(string $url): bool
    {
        if (!$this->enabled) {
            return false;
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
