<?php

namespace App\Support;

use App\Models\newProduct;
use App\Models\ProductVariant;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class ProductImageStorage
{
    public const DISK = 'public';

    public const PRODUCT_DIR = 'products';

    public const VARIANT_DIR = 'products/variants';

    public const ORIGINALS_DIR = 'products/_originals';

    public const MAX_EDGE = 500;

    public const TARGET_MIN_BYTES = 50 * 1024;

    public const TARGET_MAX_BYTES = 150 * 1024;

    /**
     * @return array{gd: bool, imagick: bool, webp: bool, driver: string|null}
     */
    public static function capabilities(): array
    {
        $gd = extension_loaded('gd');
        $imagick = extension_loaded('imagick');
        $webp = false;

        if ($gd && function_exists('imagewebp')) {
            $info = function_exists('gd_info') ? gd_info() : [];
            $webp = ! empty($info['WebP Support']) || function_exists('imagecreatefromwebp');
        }

        if (! $webp && $imagick) {
            try {
                $formats = \Imagick::queryFormats('WEBP');
                $webp = ! empty($formats);
            } catch (Throwable) {
                $webp = false;
            }
        }

        $driver = null;
        if ($gd && $webp) {
            $driver = 'gd';
        } elseif ($imagick && $webp) {
            $driver = 'imagick';
        } elseif ($gd) {
            $driver = 'gd-jpeg';
        }

        return [
            'gd' => $gd,
            'imagick' => $imagick,
            'webp' => $webp,
            'driver' => $driver,
        ];
    }

    public static function storeProductImage(UploadedFile $file): string
    {
        return self::storeOptimized($file, self::PRODUCT_DIR);
    }

    public static function storeVariantImage(UploadedFile $file): string
    {
        return self::storeOptimized($file, self::VARIANT_DIR);
    }

    /**
     * Optimize an existing storage-relative path in place (new file + caller updates DB).
     *
     * @return array{path: string, bytes: int, skipped: bool, reason?: string}
     */
    public static function optimizeExistingPath(string $relativePath, bool $force = false): array
    {
        $relativePath = self::normalizeRelativePath($relativePath);
        $disk = Storage::disk(self::DISK);

        if ($relativePath === '' || ! $disk->exists($relativePath)) {
            return ['path' => $relativePath, 'bytes' => 0, 'skipped' => true, 'reason' => 'missing'];
        }

        $bytes = (int) $disk->size($relativePath);
        $ext = strtolower(pathinfo($relativePath, PATHINFO_EXTENSION));

        if (! $force && $ext === 'webp' && $bytes >= self::TARGET_MIN_BYTES && $bytes <= self::TARGET_MAX_BYTES) {
            return ['path' => $relativePath, 'bytes' => $bytes, 'skipped' => true, 'reason' => 'already-optimized'];
        }

        $binary = $disk->get($relativePath);
        if ($binary === false || $binary === null || $binary === '') {
            return ['path' => $relativePath, 'bytes' => $bytes, 'skipped' => true, 'reason' => 'unreadable'];
        }

        $tmp = tempnam(sys_get_temp_dir(), 'prd_img_');
        if ($tmp === false) {
            throw new RuntimeException('Could not create temp file for image optimization.');
        }

        try {
            file_put_contents($tmp, $binary);
            $mime = mime_content_type($tmp) ?: 'application/octet-stream';
            $optimized = self::encodeOptimized($tmp, $mime);

            $dir = str_starts_with($relativePath, self::VARIANT_DIR)
                ? self::VARIANT_DIR
                : self::PRODUCT_DIR;

            $newPath = $dir.'/'.Str::uuid()->toString().'.'.$optimized['extension'];
            if (! $disk->put($newPath, $optimized['binary'], 'public')) {
                throw new RuntimeException('Failed to write optimized image to storage.');
            }

            return [
                'path' => $newPath,
                'bytes' => strlen($optimized['binary']),
                'skipped' => false,
            ];
        } finally {
            if (is_file($tmp)) {
                @unlink($tmp);
            }
        }
    }

    /**
     * Delete a stored image only when no product or variant still references it.
     */
    public static function deleteIfUnused(?string $path): void
    {
        $path = self::normalizeRelativePath((string) $path);
        if ($path === '') {
            return;
        }

        $stillUsed = newProduct::where('image', $path)->exists()
            || ProductVariant::where('image', $path)->exists();

        if (! $stillUsed && Storage::disk(self::DISK)->exists($path)) {
            Storage::disk(self::DISK)->delete($path);
        }
    }

    /**
     * Force-delete a path (e.g. orphan created before a failed DB update).
     */
    public static function deletePath(?string $path): void
    {
        $path = self::normalizeRelativePath((string) $path);
        if ($path === '') {
            return;
        }

        if (Storage::disk(self::DISK)->exists($path)) {
            Storage::disk(self::DISK)->delete($path);
        }
    }

    public static function url(?string $path): ?string
    {
        $path = self::normalizeRelativePath((string) $path);
        if ($path === '') {
            return null;
        }

        return Storage::disk(self::DISK)->url($path);
    }

    public static function normalizeRelativePath(string $path): string
    {
        $path = trim(str_replace('\\', '/', $path));
        if ($path === '') {
            return '';
        }

        // Strip accidental absolute /public/storage prefixes from legacy data
        $path = preg_replace('#^https?://[^/]+/storage/#i', '', $path) ?? $path;
        $path = preg_replace('#^/?storage/#i', '', $path) ?? $path;
        $path = ltrim($path, '/');

        return $path;
    }

    protected static function storeOptimized(UploadedFile $file, string $directory): string
    {
        if (! $file->isValid()) {
            throw new RuntimeException('Invalid uploaded image.');
        }

        $real = $file->getRealPath();
        if (! $real || ! is_readable($real)) {
            throw new RuntimeException('Uploaded image is not readable.');
        }

        $mime = (string) ($file->getMimeType() ?: '');
        $caps = self::capabilities();

        try {
            if ($caps['driver']) {
                $optimized = self::encodeOptimized($real, $mime);
                $path = trim($directory, '/').'/'.Str::uuid()->toString().'.'.$optimized['extension'];
                if (! Storage::disk(self::DISK)->put($path, $optimized['binary'], 'public')) {
                    throw new RuntimeException('Failed to store optimized product image.');
                }

                return $path;
            }
        } catch (Throwable $e) {
            Log::warning('Product image optimize failed; falling back to raw store.', [
                'error' => $e->getMessage(),
                'mime' => $mime,
            ]);
            // Fall through to raw store if encode failed unexpectedly
        }

        // Fallback when GD/Imagick/WebP unavailable (e.g. local XAMPP without php_gd2)
        Log::warning('Product image stored without WebP optimization — enable GD+WebP on the server.', [
            'capabilities' => $caps,
        ]);

        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        if (! in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            $ext = 'jpg';
        }
        $path = trim($directory, '/').'/'.Str::uuid()->toString().'.'.$ext;

        if (! Storage::disk(self::DISK)->putFileAs($directory, $file, basename($path), 'public')) {
            throw new RuntimeException('Failed to store product image.');
        }

        return $path;
    }

    /**
     * @return array{binary: string, extension: string}
     */
    protected static function encodeOptimized(string $absolutePath, string $mime): array
    {
        $caps = self::capabilities();

        if ($caps['driver'] === 'gd' || $caps['driver'] === 'gd-jpeg') {
            return self::encodeWithGd($absolutePath, $mime, $caps['driver'] === 'gd');
        }

        if ($caps['driver'] === 'imagick') {
            return self::encodeWithImagick($absolutePath);
        }

        throw new RuntimeException(
            'Image optimization requires PHP GD (with WebP) or Imagick. Enable php-gd on the server.'
        );
    }

    /**
     * @return array{binary: string, extension: string}
     */
    protected static function encodeWithGd(string $absolutePath, string $mime, bool $preferWebp): array
    {
        $src = self::gdCreateFromFile($absolutePath, $mime);
        if ($src === false) {
            throw new RuntimeException('Could not read image. File may be corrupt or unsupported.');
        }

        try {
            $width = imagesx($src);
            $height = imagesy($src);
            if ($width < 1 || $height < 1) {
                throw new RuntimeException('Invalid image dimensions.');
            }

            [$newW, $newH] = self::fitWithin($width, $height, self::MAX_EDGE);

            $dst = imagecreatetruecolor($newW, $newH);
            if ($dst === false) {
                throw new RuntimeException('Could not create resized image canvas.');
            }

            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
            imagefilledrectangle($dst, 0, 0, $newW, $newH, $transparent);
            imagealphablending($dst, true);

            imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $width, $height);

            if ($preferWebp && function_exists('imagewebp')) {
                $binary = self::gdExportWebpTargetSize($dst);
                imagedestroy($dst);

                return ['binary' => $binary, 'extension' => 'webp'];
            }

            // JPEG fallback when WebP not compiled into GD
            $binary = self::gdExportJpegTargetSize($dst);
            imagedestroy($dst);

            return ['binary' => $binary, 'extension' => 'jpg'];
        } finally {
            imagedestroy($src);
        }
    }

    /**
     * @return \GdImage|resource|false
     */
    protected static function gdCreateFromFile(string $path, string $mime)
    {
        $mime = strtolower($mime);
        $data = @file_get_contents($path);
        if ($data === false) {
            return false;
        }

        // imagecreatefromstring handles jpeg/png/webp/gif when GD supports them
        $img = @imagecreatefromstring($data);
        if ($img !== false) {
            return $img;
        }

        return match (true) {
            str_contains($mime, 'jpeg'), str_contains($mime, 'jpg') => @imagecreatefromjpeg($path),
            str_contains($mime, 'png') => @imagecreatefrompng($path),
            str_contains($mime, 'webp') && function_exists('imagecreatefromwebp') => @imagecreatefromwebp($path),
            str_contains($mime, 'gif') => @imagecreatefromgif($path),
            default => false,
        };
    }

    /**
     * @param  \GdImage|resource  $image
     */
    protected static function gdExportWebpTargetSize($image): string
    {
        $best = null;
        $bestLen = PHP_INT_MAX;

        foreach ([82, 75, 68, 60, 52, 45, 38] as $quality) {
            ob_start();
            imagewebp($image, null, $quality);
            $bin = (string) ob_get_clean();
            $len = strlen($bin);

            if ($len >= self::TARGET_MIN_BYTES && $len <= self::TARGET_MAX_BYTES) {
                return $bin;
            }

            if ($len < $bestLen) {
                $best = $bin;
                $bestLen = $len;
            }

            // Already under min with high quality — accept
            if ($len < self::TARGET_MIN_BYTES && $quality >= 75) {
                return $bin;
            }
        }

        if ($best === null || $best === '') {
            throw new RuntimeException('WebP encoding produced empty output.');
        }

        return $best;
    }

    /**
     * @param  \GdImage|resource  $image
     */
    protected static function gdExportJpegTargetSize($image): string
    {
        $best = null;
        $bestLen = PHP_INT_MAX;

        foreach ([85, 78, 70, 62, 55, 48, 40] as $quality) {
            ob_start();
            imagejpeg($image, null, $quality);
            $bin = (string) ob_get_clean();
            $len = strlen($bin);

            if ($len >= self::TARGET_MIN_BYTES && $len <= self::TARGET_MAX_BYTES) {
                return $bin;
            }

            if ($len < $bestLen) {
                $best = $bin;
                $bestLen = $len;
            }

            if ($len < self::TARGET_MIN_BYTES && $quality >= 78) {
                return $bin;
            }
        }

        if ($best === null || $best === '') {
            throw new RuntimeException('JPEG encoding produced empty output.');
        }

        return $best;
    }

    /**
     * @return array{binary: string, extension: string}
     */
    protected static function encodeWithImagick(string $absolutePath): array
    {
        $img = new \Imagick($absolutePath);
        try {
            $img->setImageOrientation(\Imagick::ORIENTATION_TOPLEFT);
            if (method_exists($img, 'autoOrient')) {
                $img->autoOrient();
            }

            $w = $img->getImageWidth();
            $h = $img->getImageHeight();
            [$newW, $newH] = self::fitWithin($w, $h, self::MAX_EDGE);
            if ($newW !== $w || $newH !== $h) {
                $img->resizeImage($newW, $newH, \Imagick::FILTER_LANCZOS, 1, true);
            }

            $img->setImageFormat('webp');
            $best = null;
            $bestLen = PHP_INT_MAX;

            foreach ([82, 75, 68, 60, 52, 45, 38] as $quality) {
                $img->setImageCompressionQuality($quality);
                $bin = $img->getImagesBlob();
                $len = strlen($bin);

                if ($len >= self::TARGET_MIN_BYTES && $len <= self::TARGET_MAX_BYTES) {
                    return ['binary' => $bin, 'extension' => 'webp'];
                }

                if ($len < $bestLen) {
                    $best = $bin;
                    $bestLen = $len;
                }

                if ($len < self::TARGET_MIN_BYTES && $quality >= 75) {
                    return ['binary' => $bin, 'extension' => 'webp'];
                }
            }

            if ($best === null || $best === '') {
                throw new RuntimeException('Imagick WebP encoding failed.');
            }

            return ['binary' => $best, 'extension' => 'webp'];
        } finally {
            $img->clear();
            $img->destroy();
        }
    }

    /**
     * @return array{0: int, 1: int}
     */
    protected static function fitWithin(int $width, int $height, int $maxEdge): array
    {
        $maxDim = max($width, $height);
        if ($maxDim <= $maxEdge) {
            return [$width, $height];
        }

        $scale = $maxEdge / $maxDim;

        return [
            max(1, (int) round($width * $scale)),
            max(1, (int) round($height * $scale)),
        ];
    }
}
