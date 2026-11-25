<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;

class ImageCompressHelper
{
    /**
     * Resolusi maksimum sisi terpanjang (Full HD: 1600px).
     * Sangat tajam untuk katalog web desktop, tablet kasir POS, dan layar retina HP.
     */
    public const DEFAULT_MAX_DIMENSION = 1600;

    /**
     * Kualitas kompresi 85%:
     * Keseimbangan optimal antara kualitas visual HD tanpa artefak kompresi
     * dan penurunan ukuran file drastis hingga 80-95%.
     */
    public const DEFAULT_QUALITY = 85;

    /**
     * Kompresi dan simpan foto ke storage disk publik.
     *
     * @param  UploadedFile  $file          File foto yang diunggah
     * @param  string        $folder        Folder tujuan (mis. 'produk', 'varian-rasa/PRD001')
     * @param  string        $disk          Disk penyimpanan Laravel (default: 'public')
     * @param  int           $maxDimension  Batas dimensi sisi terpanjang (default: 1600px)
     * @param  int           $quality       Kualitas kompresi 1-100 (default: 85)
     * @return string Path relatif file yang tersimpan
     */
    public static function compressAndStore(
        UploadedFile $file,
        string $folder,
        string $disk = 'public',
        int $maxDimension = self::DEFAULT_MAX_DIMENSION,
        int $quality = self::DEFAULT_QUALITY
    ): string {
        try {
            $manager = new ImageManager(GdDriver::class);
            $image = $manager->decodePath($file->getPathname());

            // 1. Orientasi otomatis berdasarkan metadata EXIF (misal jepretan kamera HP)
            if (method_exists($image, 'orient')) {
                $image->orient();
            }

            // 2. Skala turun proporsional hanya jika gambar melebihi dimensi HD (1600px)
            // Menjaga aspect ratio asli tanpa distorsi atau stretch
            $image->scaleDown(width: $maxDimension, height: $maxDimension);

            // 3. Encode ke format WebP berkualitas tinggi (mendukung transparansi PNG & kompresi efisien)
            // Fallback ke JPEG jika driver GD di lingkungan tertentu tidak mendukung WebP
            $encoder = function_exists('imagewebp')
                ? new WebpEncoder(quality: $quality)
                : new JpegEncoder(quality: $quality);

            $extension = function_exists('imagewebp') ? 'webp' : 'jpg';

            $encoded = $image->encode($encoder);

            // 4. Buat nama file unik dan simpan ke disk
            $filename = trim($folder, '/') . '/' . Str::random(40) . '.' . $extension;

            Storage::disk($disk)->put($filename, (string) $encoded);

            return $filename;
        } catch (\Throwable $e) {
            // Fallback aman ke penyimpanan standar jika terjadi kendala pemrosesan
            report($e);
            return $file->store($folder, $disk);
        }
    }
}
