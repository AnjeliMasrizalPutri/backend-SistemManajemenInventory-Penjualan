<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $client;
    protected $apiKey;
    protected $adminNumber;
    protected $deviceId;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://api.fonnte.com/', // Base URL Fonnte API
            'timeout'  => 5.0, // Timeout request dalam detik
        ]);
        $this->apiKey = env('FONNTE_API_KEY');
        $this->adminNumber = env('ADMIN_WHATSAPP_NUMBER');
        // $this->deviceId = env('FONNTE_DEVICE_ID');
    }

    /**
     * Mengirim pesan WhatsApp ke nomor admin.
     *
     * @param string $message
     * @return bool
     */
    public function sendNotification(string $message): bool
    {
        if (empty($this->apiKey)) {
            Log::error('FONNTE_API_KEY belum dikonfigurasi di .env.');
            return false;
        }

        if (empty($this->adminNumber)) {
            Log::error('ADMIN_WHATSAPP_NUMBER belum dikonfigurasi di .env.');
            return false;
        }

        try {
            $data = [
                'target' => $this->adminNumber,
                'message' => $message,
            ];

            if (!empty($this->deviceId)) {
                $data['device'] = $this->deviceId;
            }

            $response = $this->client->post('send', [ // Endpoint 'send'
                'headers' => [
                    'Authorization' => $this->apiKey,
                ],
                'form_params' => $data,
            ]);

            $statusCode = $response->getStatusCode();
            $body = json_decode($response->getBody()->getContents(), true);

            if ($statusCode == 200 && isset($body['status']) && $body['status']) {
                Log::info('Notifikasi WhatsApp berhasil dikirim.', ['response' => $body]);
                return true;
            } else {
                Log::error('Gagal mengirim notifikasi WhatsApp.', [
                    'status_code' => $statusCode,
                    'response' => $body,
                ]);
                return false;
            }
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $errorMessage = $e->getMessage();
            if ($e->hasResponse()) {
                $errorMessage .= ' - Response: ' . $e->getResponse()->getBody()->getContents();
            }
            Log::error('Terjadi kesalahan request Guzzle saat mengirim notifikasi WhatsApp: ' . $errorMessage);
            return false;
        } catch (\Exception $e) {
            Log::error('Terjadi kesalahan umum saat mengirim notifikasi WhatsApp: ' . $e->getMessage());
            return false;
        }
    }
}
