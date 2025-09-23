<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class PepipostMailService
{
    private $apiKey;
    private $apiUrl;

    public function __construct()
    {
        $this->apiKey = '7cbc4298031ff6ab550c0f8f438af85a';
        $this->apiUrl = 'https://api.pepipost.com/v2/sendEmail';
    }

    /**
     * Send email using Pepipost API
     */
    public function sendEmail($to, $cc, $subject, $htmlContent, $fromEmail = 'junioreditor@dbcorp.in', $fromName = 'JE 7')
    {
        try {
            $data = [
                'personalizations' => [
                    [
                        'recipient' => $to,
                        'recipient_cc' => $cc
                    ]
                ],
                'from' => [
                    'fromEmail' => $fromEmail,
                    'fromName' => $fromName
                ],
                'subject' => $subject,
                'content' => $htmlContent
            ];

            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL => $this->apiUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => json_encode($data, true),
                CURLOPT_HTTPHEADER => [
                    "api_key: " . $this->apiKey,
                    "content-type: application/json"
                ],
            ]);

            $response = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $error = curl_error($curl);

            curl_close($curl);

            if ($error) {
                Log::error('Pepipost cURL Error: ' . $error);
                return false;
            }

            if ($httpCode !== 200 && $httpCode !== 202) {
                Log::error('Pepipost API Error - HTTP Code: ' . $httpCode . ', Response: ' . $response);
                return false;
            }

            Log::info('Pepipost email sent successfully', ['response' => $response]);
            return true;

        } catch (\Exception $e) {
            Log::error('Pepipost email sending failed: ' . $e->getMessage());
            return false;
        }
    }
}
