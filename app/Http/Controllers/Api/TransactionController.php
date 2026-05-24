<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Midtrans\Config;
use Midtrans\Snap;

class TransactionController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = false;
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

     //FUNGSI CHECKOUT
    public function checkout(Request $request)
    {
        $authUser = $request->attributes->get('auth_user');
        if (!$authUser) {
            return response()->json(['success' => false, 'message' => 'Sesi tidak valid, silakan login ulang.'], 401);
        }

        $validator = Validator::make($request->all(), [
            'event_id'    => 'required|integer',
            'jenis_tiket' => 'required|in:vip,reguler',
            'nomor_kursi' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        $event = Event::find($request->event_id);
        if (!$event) {
            return response()->json(['success' => false, 'message' => 'Event tidak ditemukan.'], 404);
        }

        if ($event->seats && json_decode($event->seats) === true && empty($request->nomor_kursi)) {
            return response()->json(['success' => false, 'message' => 'Nomor kursi wajib dipilih untuk event ini.'], 400);
        }

        // Pengecekan sisa kuota disesuaikan dengan jenis tiket yang dipilih
        $sisaKuota = ($request->jenis_tiket === 'vip') ? $event->kapasitas_vip : $event->kapasitas_reg;
        if ($sisaKuota < 1) {
            return response()->json(['success' => false, 'message' => 'Maaf, kuota tiket untuk kelas ini sudah habis.'], 400);
        }

        $jumlahTiket = 1;
        $hargaSatuan = ($request->jenis_tiket === 'vip') ? $event->harga_vip : $event->harga_reg;
        $totalHarga  = $hargaSatuan * $jumlahTiket;

        $kodeTransaksi = 'EVT-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

        $midtransPayload = [
            'transaction_details' => [
                'order_id'     => $kodeTransaksi,
                'gross_amount' => (int) $totalHarga,
            ],
            'customer_details' => [
                'first_name' => $authUser->nama,
                'email'      => $authUser->email,
            ],
            'item_details' => [
                [
                    'id'       => $event->id,
                    'price'    => (int) $hargaSatuan,
                    'quantity' => 1,
                    'name'     => 'Tiket ' . ucfirst($request->jenis_tiket) . ' ' . ($request->nomor_kursi ? '('.$request->nomor_kursi.')' : ''),
                ]
            ],
        ];

        DB::beginTransaction();
        try {
            $snapToken = Snap::getSnapToken($midtransPayload);

            $transaction = Transaction::create([
                'kode_transaksi'    => $kodeTransaksi,
                'user_id'           => $authUser->id,
                'event_id'          => $event->id,
                'jenis_tiket'       => $request->jenis_tiket,
                'nomor_kursi'       => $request->nomor_kursi,
                'jumlah_tiket'      => 1,
                'total_harga'       => $totalHarga,
                'status_pembayaran' => 'pending',
                'status_kehadiran'  => 'belum_hadir',
                'snap_token'        => $snapToken,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sesi pembayaran berhasil dibuat.',
                'data'    => [
                    'kode_transaksi' => $transaction->kode_transaksi,
                    'snap_token'     => $snapToken
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal terhubung ke gateway pembayaran.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * FUNGSI NOTIFICATION HANDLER - PERBAIKAN LOGIKA TOTAL
     */
    public function notificationHandler(Request $request)
    {
        try {
            $notif = new \Midtrans\Notification();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid Notification Payload'], 400);
        }

        $transactionStatus = $notif->transaction_status;
        $orderId           = $notif->order_id; // Perbaikan capitalization: $orderId (bukan $orderid)
        $statusPembayaran  = 'pending';

        // 1. Tentukan status pembayaran dari Midtrans dahulu
        if ($transactionStatus == 'capture' || $transactionStatus == 'settlement') {
            $statusPembayaran = 'success'; 
        } elseif ($transactionStatus == 'deny' || $transactionStatus == 'cancel') {
            $statusPembayaran = 'failed';
        } elseif ($transactionStatus == 'expire') {
            $statusPembayaran = 'expired';
        }

        // 2. Cari data transaksi di database lokal berdasarkan kode_transaksi
        $transaction = Transaction::where('kode_transaksi', $orderId)->first();

        if ($transaction) {
            // 3. Logika pengurangan kapasitas tiket dijalankan HANYA JIKA status berubah menjadi success
            if ($statusPembayaran === 'success' && $transaction->status_pembayaran !== 'success') {
                $event = Event::find($transaction->event_id);
                if ($event) {
                    if ($transaction->jenis_tiket === 'vip') {
                        $event->decrement('kapasitas_vip', 1);
                    } else {
                        $event->decrement('kapasitas_reg', 1);
                    }
                }
            }

            // 4. Perbarui status pembayaran transaksi di database lokal
            $transaction->update([
                'status_pembayaran' => $statusPembayaran
            ]);

            return response()->json(['message' => 'Status database berhasil diperbarui.'], 200);
        }

        return response()->json(['message' => 'Data transaksi tidak ditemukan.'], 404);
    }

    public function getMyTickets(Request $request)
    {
        // 1. Ambil data user dari JWT yang dilewatin middleware
        $authUser = $request->attributes->get('auth_user');
        if (!$authUser) {
            return response()->json(['success' => false, 'message' => 'Sesi tidak valid.'], 401);
        }

        // 2. Ambil transaksi milik user tersebut yang statusnya SUDAH BERHASIL (Lunas)
        // Kita sertakan data 'event' menggunakan eager loading (with)
        $tickets = \App\Models\Transaction::with('event')
            ->where('user_id', $authUser->id)
            ->where('status_pembayaran', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar tiket berhasil dimuat.',
            'data' => $tickets
        ], 200);
    }

    public function showDetailTicket(Request $request, $kode_transaksi)
    {
        $authUser = $request->get('auth_user');
        if (!$authUser) {
            return response()->json(['success' => false, 'message' => 'Sesi Kadaluarsa.'], 401);
        }

        $ticket = \App\Models\Transaction::with('event')->where('kode_transaksi', $kode_transaksi)->where('user_id', $authUser->id)->first();

        if (!$ticket) {
            return response()->json(['success' => false, 'message' => 'Tiket tidak ditemukan.'], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail tiket berhasil ditemukan.',
            'data' => $ticket
        ], 200);
    }
}

