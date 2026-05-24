<?php

namespace App\Http\Controllers\Api;

use App\Models\Event;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentGatewayController extends Controller
{
    public function requestInvoice(Request $request)
    {
        try {
            // $authUser = $request->attributes->get('auth_user');
            $User = $request->get('auth_user');

            $authUser = \App\Models\User::find($User->id);

            if (!$authUser) {
                return response()->json(['success' => false, 'message' => 'Sesi tidak valid'], 401);
            }

            $event = Event::find($request->event_id);
            if (!$event) {
                return response()->json(['success' => false, 'message' => 'Event tidak ditemukan.'], 404);
            }

            $harga = $request->jenis_tiket === 'vip' ? $event->harga_vip : $event->harga_reg;

            $kodeTransaksi = 'EVT-' . strtoupper(Str::random(8));
            $merchantCode = 'D0001';
            $apiKey = '1234567890abcdef1234567890abcdef';
            $strtoHash = $merchantCode . $kodeTransaksi . $harga . $apiKey;
            $signature = hash('sha256', $strtoHash);
            $paymentAmount = 5000;

            $payload = [
                'merchantCode' => $merchantCode,
                'paymentAmount' => $paymentAmount,
                'merchantOrderId' => $kodeTransaksi,
                'productDetails' => 'Tiket Masuk EventIn',
                'email' => $authUser->email ?? 'user_test@eventin.com',
                'phoneNumber' => '08123456789',
                'additionalParam' => '',
                'merchantUserInfo' => 'User EventIn',
                'callbackUrl' => 'https://lewatmana.com/callback',
                'returnUrl' => 'https://lewatmana.com/success',
                'signature' => $strtoHash,
                'expiryPeriod' => 60 
            ];

            $response = Http::withHeaders([
                'Content-Type' => 'application/json'
            ])->withoutVerifying()->timeout(10)->post('https://sandbox.duitku.com/webapi/api/merchant/v2/inquiry', $payload);

                // 2. KODE SUPER DEBUG: Catat semua indikator status ke laravel.log
            Log::info('--- DEBUG DUITKU START ---');
            Log::info('Status HTTP Code: ' . $response->status()); // Mengambil angka status (misal: 400, 404, 500)
            Log::info('Isi Body Mentah: ' . ($response->body() ?: 'BODY KOSONG SAMA SEKALI'));
            Log::info('Isi JSON Array: ' . json_encode($response->json()));
            Log::info('--- DEBUG DUITKU END ---');

            if ($response->successful() && isset($response['paymentUrl'])) {

            Transaction::create([
                'kode_transaksi' => $kodeTransaksi,
                'user_id' => $authUser->id,
                'event_id' => $event->id,
                'jenis_tiket' => 1,
                'total_harga' => $harga,
                'status_pembayaran' => 'pending',
                'status_kehadiran' => 'belum_hadir',
                'payment_url' => $response['paymentUrl']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Invoice berhasil dibuat.',
                'data' => [
                    'payment_url' => $request['paymentUrl'],
                    'kode_transaksi' => $kodeTransaksi
                ]
            ], 200);
            }

            Log::error('Duitku Inquiry Failed: ' . $response->body());
            return response()->json(['success' => false, 'message' => 'Gagal meminta tagihan.'], 500);


        } catch (\Exception $e) {
            Log::error('Duitku Controller Error: ' . $e->getMessage());
            Log::error('Trace Error: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'terjadi keasalahan internal error.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
