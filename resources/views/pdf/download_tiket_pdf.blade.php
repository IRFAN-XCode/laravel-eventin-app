<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>E-Ticket</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            background-color: #f5f5f5;
            padding: 20px;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border: 1px solid #dddddd;
            border-radius: 8px;
            overflow: hidden;
        }
        .header {
            background-color: #16e2eb;
            color: #ffffff;
            padding: 20px;
            text-align: center;
        }
        .content {
            padding: 30px;
        }
        .title {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 20px;
            text-align: center;
        }
        table {
            width: 100%;
            margin-top: 10px;
        }
        table td {
            padding: 12px;
            border-bottom: 1px dashed #cccccc;
            font-size: 14px;
        }
        .label {
            color: #666666;
            font-weight: normal;
        }
        .value {
            text-align: right;
            font-weight: bold;
        }
        .qr-section {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px dashed #16e2eb;
        }
        .qr-code {
            width: 220px;
            height: 220px;
            margin-bottom: 10px;
        }
        .footer {
            background-color: #f7fafc;
            padding: 15px;
            text-align: center;
            font-size: 11px;
            color: #a0aec0;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="header" style="display: flex; align-items: center; justify-content: space-between; gap: 20px; padding: 15px 0;">
    
            <div class="image">
                <img src="../resources/images/eventin.png" alt="Logo EventIn" style="width: 80px; height: auto; margin-bottom: 10px;">
            </div>

            <div class="text-title" style="display: flex; flex-direction: column;">
                <h2 style="margin: 0; font-size: 20px; color: #ffffff;">EVENTIN E-TICKET</h2>
                <p style="margin: 5px 0 0 0; font-size: 12px; color: #ffffff; font-weight: 600;">
                    Kode: {{ $transaction->kode_transaksi }}
                </p>
            </div>
        </div>
        
        <div class="content">
            <div class="title">{{ $event->nama_event ?? 'Nama Event Tidak Tersedia' }}</div>
                        
            <table>
                <tr>
                    <td class="label">Nama Pemesan</td>
                    <td class="value">{{ $user->nama }}</td>
                </tr>
                <tr>
                    <td class="label">Email Pemesan</td>
                    <td class="value">{{ $user->email }}</td>
                </tr>
                <tr>
                    <td class="label">Nomor Handphone</td>
                    <td class="value">{{ $user->nomor_handphone }}</td>
                </tr>
                <tr>
                    <td class="label">Kategori Tiket</td>
                    <td class="value" style="text-transform: uppercase;">{{ $transaction->jenis_tiket }} Class</td>
                </tr>
                <tr>
                    <td class="label">Nomor Kursi</td>
                    <td class="value">{{ $transaction->nomor_kursi ? $transaction->nomor_kursi : 'Free Seating' }}</td>
                </tr>
                <tr>
                    <td class="label">Tanggal Acara</td>
                    <td class="value">{{ \Carbon\Carbon::parse($event->tgl_event)->isoFormat('D MMMM YYYY') }}</td>
                </tr>
                <tr>
                    <td class="label">Status Pembayaran</td>
                    <td class="value" style="color: green;">LUNAS / VALID</td>
                </tr>
            </table>

            <div class="qr-section">
                <img class="qr-code" src="{{ $qr_code }}" alt="QR Code Tiket">
                <p style="margin: 5px 0 0 0; font-size: 11px; color: #888888; font-weight: bold;">
                    Pindai QR Code saat *Check-In* di Lokasi Acara
                </p>
            </div>
        </div>
    </div>

    <div class="footer">
        Dicetak secara digital melalui Aplikasi EventIn pada {{ $tanggal_cetak }}.<br>
        Harap bawa dan tunjukkan berkas PDF ini saat memasuki pintu masuk acara untuk divalidasi.
    </div>

</body>
</html>