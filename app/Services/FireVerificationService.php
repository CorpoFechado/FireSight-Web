<?php

namespace App\Services;

use CURLFile;
use Illuminate\Support\Facades\Log;

class FireVerificationService
{
    /**
     * Calls the local FIRESIGHT fire-verification-service (FastAPI running on
     * http://localhost:8000) to classify an uploaded report photo.
     *
     * This NEVER blocks report submission: if the service is down, times out,
     * or errors, this returns null values and the report is still saved as
     * normal (just without an AI flag) — that's the intended behavior.
     *
     * @param  string  $absoluteImagePath  Full filesystem path to the saved image.
     * @return array{label: ?string, confidence: ?float}
     */
    public function verifyFireImage(string $absoluteImagePath): array
    {
        $result = ['label' => null, 'confidence' => null];

        if (! file_exists($absoluteImagePath)) {
            Log::warning('[FireVerificationService] Image not found: '.$absoluteImagePath);

            return $result;
        }

        $serviceUrl = config('services.fire_verification.url', 'http://localhost:8000/predict');
        $mimeType = mime_content_type($absoluteImagePath) ?: 'image/jpeg';

        try {
            $curlFile = new CURLFile($absoluteImagePath, $mimeType, basename($absoluteImagePath));

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $serviceUrl,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => ['image' => $curlFile],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_CONNECTTIMEOUT => 3,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                Log::info('[FireVerificationService] cURL error: '.$curlError);

                return $result;
            }

            if ($httpCode !== 200) {
                Log::info('[FireVerificationService] Service returned HTTP '.$httpCode.': '.$response);

                return $result;
            }

            $data = json_decode((string) $response, true);
            if (! is_array($data) || ! isset($data['label'], $data['confidence'])) {
                Log::info('[FireVerificationService] Unexpected response: '.$response);

                return $result;
            }

            $result['label'] = (string) $data['label']; // 'fire' or 'non_fire'
            $result['confidence'] = (float) $data['confidence'];

            return $result;
        } catch (\Throwable $e) {
            Log::warning('[FireVerificationService] Exception during verification: '.$e->getMessage());

            return $result;
        }
    }
}
