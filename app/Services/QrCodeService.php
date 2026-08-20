<?php

namespace App\Services;

use App\Models\Certificate;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Illuminate\Support\Facades\Storage;

class QrCodeService
{
    /**
     * Generates a QR PNG encoding the certificate's public verification URL,
     * stores it to disk, and returns the storage-relative path (saved on
     * certificates.qr_path). Called once at issue time, never regenerated.
     */
    public function generateForCertificate(Certificate $certificate): string
    {
        $url = rtrim(config('app.url'), '/')
            . '/' . config('handseal.verify_path')
            . '/' . $certificate->certificate_number;

        $writer = new \Endroid\QrCode\Writer\PngWriter();

        $qrCode = new \Endroid\QrCode\QrCode(
            data: $url,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: config('handseal.qr_size'),
            margin: 8,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
        );

        $result = $writer->write($qrCode);

        $path = config('handseal.qr_path') . '/' . $certificate->certificate_number . '.png';

        Storage::disk(config('handseal.qr_disk'))->put($path, $result->getString());

        return $path;
    }
}
