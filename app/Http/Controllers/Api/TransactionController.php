<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Event;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
// use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use SimpleSoftwareIO\QrCode\Facades\QrCode;


class TransactionController extends Controller
{
    public function checkout(Request $request)
    {
        $jwtUser = $request->attributes->get('auth_user');
        if (!$jwtUser) {
            return response()->json(['success' => false, 'message' => 'Sesi tidak valid, silakan login ulang.'], 401);
        }

        $authUser = User::find($jwtUser->id);
        if (empty($authUser->nama) || empty($authUser->email) || empty($authUser->jenis_kelamin) || empty($authUser->nomor_handphone)) {
            return response()->json(['success' => false, 'message' => 'Profil Anda belum lengkap! Silakan lengkapi di menu profil.'], 400);
        }

        $validator = Validator::make($request->all(), [
            'event_id'         => 'required|integer',
            'jenis_tiket'      => 'required|in:vip,reguler',
            'nomor_kursi'      => 'nullable|string|max:50',
            'bank_pengirim'    => 'required|string|max:100',
            'atas_nama'        => 'required|string|max:100',
            'bukti_pembayaran' => 'required|image|mimes:jpeg,png,jpg|max:3072',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $event = Event::find($request->event_id);
        if (!$event) {
            return response()->json(['success' => false, 'message' => 'Event tidak ditemukan.'], 404);
        }

        // Hitung harga
        $hargaSatuan = ($request->jenis_tiket === 'vip') ? $event->harga_vip : $event->harga_reg;
        $kodeTransaksi = 'EVT-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

        DB::beginTransaction();
        try {
            // Proses Upload File Gambar ke folder public/storage/bukti_bayar
            if ($request->hasFile('bukti_pembayaran')) {
                $file = $request->file('bukti_pembayaran');
                $namaFile = $kodeTransaksi . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('bukti_bayar', $namaFile, 'public');
            }

            $transaction = Transaction::create([
                'kode_transaksi'    => $kodeTransaksi,
                'user_id'           => $authUser->id,
                'event_id'          => $event->id,
                'jenis_tiket'       => $request->jenis_tiket,
                'nomor_kursi'       => $request->filled('nomor_kursi') ? $request->nomor_kursi : null,
                'jumlah_tiket'      => 1,
                'total_harga'       => $hargaSatuan,
                'bank_pengirim'     => $request->bank_pengirim,
                'atas_nama'         => $request->atas_nama,
                'bukti_pembayaran'  => $path ?? null,
                'status_pembayaran' => 'checking_admin',
                'status_kehadiran'  => 'belum_hadir',
            ]);

            DB::commit();

            // TRIGGER TELEGRAM NOTIFIKASI KE ADMIN (Sesuai konsep nomor 3)
            $this->kirimNotifTelegram($transaction, $event, $authUser);

            return response()->json([
                'success' => true,
                'message' => 'Bukti pembayaran berhasil diunggah. Menunggu konfirmasi admin.',
                'data'    => ['kode_transaksi' => $kodeTransaksi]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan transaksi.', 'error' => $e->getMessage()], 500);
        }
    }

    public function konfirmasiStatusAdmin(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:success,failed'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Status tidak valid.'], 422);
        }

        $transaction = Transaction::find($id);
        if (!$transaction) {
            return response()->json(['success' => false, 'message' => 'Transaksi tidak ditemukan.'], 404);
        }

        if ($transaction->status_pembayaran === 'success') {
            return response()->json(['success' => false, 'message' => 'Transaksi ini sudah berstatus sukses sebelumnya.'], 400);
        }

        DB::beginTransaction();
        try {
            if ($request->status === 'success') {
                $event = Event::find($transaction->event_id);
                if ($event) {
                    if ($transaction->jenis_tiket === 'vip') {
                        $event->decrement('kapasitas_vip', 1);
                    } else {
                        $event->decrement('kapasitas_reg', 1);
                    }
                }
            }

            $transaction->update([
                'status_pembayaran' => $request->status
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Status transaksi berhasil diubah menjadi ' . strtoupper($request->status)], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal memperbarui status.', 'error' => $e->getMessage()], 500);
        }
    }

    private function kirimNotifTelegram($transaction, $event, $user)
    {
        $token = env('TELEGRAM_BOT_TOKEN');
        $chatId = env('TELEGRAM_ADMIN_CHAT_ID');

        if (!$token || !$chatId) return;

        // Susun teks rapi berformat Markdown untuk layar HP Admin
        $pesan = "*ADA PEMBAYARAN BARU (EventIn)* 🚨\n\n"
               . "• *Kode Transaksi:* `{$transaction->kode_transaksi}`\n"
               . "• *Nama Pembeli:* {$user->nama}\n"
               . "• *Nama Event:* {$event->nama_event}\n"
               . "• *Kategori Tiket:* " . strtoupper($transaction->jenis_tiket) . "\n"
               . "• *Total Tagihan:* Rp " . number_format($transaction->total_harga, 0, ',', '.') . "\n\n"
               . "*DATA REKENING PENGIRIM:*\n"
               . "• *Bank Asal:* {$transaction->bank_pengirim}\n"
               . "• *Atas Nama:* {$transaction->atas_nama}\n\n"
               . "*Status saat ini:* `Checking Admin`\n"
               . "Silakan buka Dashboard Admin EventIn untuk memeriksa keaslian bukti gambar dan mengubah status konfirmasi.";

        Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
            'chat_id'    => $chatId,
            'text'       => $pesan,
            'parse_mode' => 'Markdown'
        ]);
    }
    

    public function getMyTickets(Request $request)
    {
        // 1. Ambil data user dari JWT yang dilewatin middleware
        $authUser = $request->attributes->get('auth_user');
        if (!$authUser) {
            return response()->json(['success' => false, 'message' => 'Sesi tidak valid.'], 401);
        }

        $tickets = \App\Models\Transaction::with('event')
            ->where('user_id', $authUser->id)
            ->whereIn('status_pembayaran', ['success', 'checking_admin'])
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

    public function downloadTiketPDF($kode_transaksi) 
    {
        try {
            $transaction = \App\Models\Transaction::where('kode_transaksi', $kode_transaksi)->first();

            if (!$transaction) {
                return response()->json(['success' => false, 'message' => 'Tiket tidak ditemukan.'], 404);
            }

            $user = \App\Models\User::find($transaction->user_id);
            $event = \App\Models\Event::find($transaction->event_id);

            $qrCodeData = QrCode::format('svg')
                                    ->size(150)
                                    ->margin(1)
                                    ->generate($transaction->kode_transaksi);

            $qrCodebase64 = 'data:image/png;base64,' . base64_encode($qrCodeData);

            $data = [
                'transaction' => $transaction,
                'user' => $user,
                'event' => $event,
                'qr_code' => $qrCodebase64,
                'tanggal_cetak' => date('d M Y - H:i')
            ];

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.download_tiket_pdf', $data);
            $pdf->setPaper('a4', 'potrait');

            return $pdf->download('Tiket-' . $transaction->kode_transaksi . '.pdf');
        } catch (\Exception $e) {
            \Log::error('Error Cetak PDF: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
            

