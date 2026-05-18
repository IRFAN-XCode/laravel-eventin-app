<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// use Illuminate\Support\Facades\DB;
use App\Models\Event;
// use Illuminate\Support\Facades\Validator;

// Laravel Romi (2)
class EventController extends Controller
{
    /**
     * Menampilkan semua data event (Untuk Request GET)
     */
    public function index()
    {
        // Menggunakan Eloquent dengan relasi organizer dan filter status 'open'
        $events = Event::with('organizer:id,nama_eo') 
            ->select('id', 'nama_event', 'tgl_event', 'harga_reg', 'organizer_id')
            ->where('status', 'open') 
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar event aktif berhasil diambil.',
            'data'    => $events
        ], 200);
    }

    /**
     * Menyimpan data event baru (Untuk Request POST)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
        'organizer_id'  => 'required|integer',
        'nama_event'    => 'required|string',
        'waktu'         => 'required|date_format:H:i:s', // Validasi format jam
        'tgl_event'     => 'required|date_format:Y-m-d', // Validasi format tanggal
        'harga_vip'     => 'required|numeric',
        'harga_reg'     => 'required|numeric',
        'lokasi'        => 'required|string',
        'seats'         => 'required|integer',
        'thumbnail'     => 'nullable|string',
        'kapasitas_vip' => 'required|integer',
        'kapasitas_reg' => 'required|integer',
        'kategori'      => 'required|string',
        'status'        => 'required|string',
    ]);

    // Simpan ke database
    Event::create($validated);

    return response()->json(['message' => 'Event berhasil dibuat!'], 21);
        // // 1. Validasi inputan
        // $validator = Validator::make($request->all(), [
        //     'nama_event'   => 'required|string|max:255',
        //     'organizer_id' => 'required|integer',
        //     'tgl_event'    => 'required|date',
        //     'harga_reg'    => 'required|integer|min:0',
        // ]);

        // // 2. Jika validasi gagal
        // if ($validator->fails()) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Validasi gagal.',
        //         'errors'  => $validator->errors()
        //     ], 422);
        // }

        // // 3. Jika validasi lolos, insert data ke database menggunakan Query Builder
        // // Sesuai logika kamu yang menggunakan DB::table
        // $data = [
        //     'nama_event'   => $request->nama_event,
        //     'organizer_id' => $request->organizer_id,
        //     'tgl_event'    => $request->tgl_event,
        //     'harga_reg'    => $request->harga_reg,
        //     'lokasi'       => $request->lokasi ?? '-', // Menambah default jika kolom ini wajib di database
        //     'status'       => 'open',
        //     'created_at'   => now(),
        //     'updated_at'   => now(),
        // ];

        // DB::table('events')->insert($data);

        // // 4. Kirim respon sukses
        // return response()->json([
        //     'success' => true,
        //     'message' => 'Event baru berhasil ditambahkan!',
        //     'data'    => $data
        // ], 201);
    }
}