<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// use Illuminate\Support\Facades\DB;
use App\Models\Event;
use App\Models\Organizer;
use Illuminate\Support\Facades\Validator;
// use Illuminate\Support\Facades\Storage;

// Laravel Romi (2)
class EventController extends Controller
{
    
    public function index()
    {
        // Menggunakan Eloquent dengan relasi organizer dan filter status 'open'
        $events = Event::with('organizer:id,nama_eo') 
            ->select('id', 'nama_event', 'tgl_event', 'harga_reg', 'organizer_id', 'thumbnail')
            ->where('status', 'open') 
            ->orderBy('tgl_event', 'asc') 
            ->get();

        $events->map(function ($event) {
            if ($event->thumbnail) {
                $event->poster_url = asset('storage/' . $event->thumbnail);
            } else {
                $event->poster_url = 'https://via.placeholder.com/150';
            }
            return $event;
        });

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
        // 1. Ambil data user dari JWT Middleware kustom
        $authUser = $request->attributes->get('auth_user');

        if (!$authUser || $authUser->role !== 'organizer') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak! Hanya Organizer yang dapat membuat event.'
            ], 403);
        }

        // 2. Cari organizer_id dari tabel 'organizers' berdasarkan user_id token
        $organizer = Organizer::where('user_id', $authUser->id)->first();

        if (!$organizer) {
            return response()->json([
                'success' => false,
                'message' => 'Profil Organizer Anda tidak ditemukan.'
            ], 404);
        }

        // 3. Validasi input
        $validator = Validator::make($request->all(), [
            'nama_event'    => 'required|string|max:255',
            'waktu'         => 'required|date_format:H:i',
            'tgl_event'     => 'required|date_format:Y-m-d|after_or_equal:today',
            'harga_vip'     => 'required|numeric|min:0',
            'harga_reg'     => 'required|numeric|min:0',
            'lokasi'        => 'required|string',
            'seats'         => 'required|integer|min:1',
            'kapasitas_vip' => 'required|integer|min:0',
            'kapasitas_reg' => 'required|integer|min:0',
            'kategori'      => 'required|string|in:music,theater,workshop',
            'thumbnail'  => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'nama_event.required' => 'Nama event wajib diisi.',
            'tgl_event.after_or_equal' => 'Tanggal event tidak boleh di masa lampau.',
            'thumbnail.required' => 'Poster atau banner event wajib diunggah.',
            'thumbnail.image' => 'Berkas berkategori poster harus berupa gambar.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        // 4. Proses upload file poster event ke folder storage/app/public/events
        $posterPath = null;
        if ($request->hasFile('thumbnail')) {
            $file = $request->file('thumbnail');
            $posterPath = $file->store('events', 'public');
        }

        // 5. Menyusun data lengkap untuk disimpan ke database
        $eventData = [
            'organizer_id'  => $organizer->id,
            'nama_event'    => $request->nama_event,
            'waktu'         => $request->waktu,
            'tgl_event'     => $request->tgl_event,
            'harga_vip'     => $request->harga_vip,
            'harga_reg'     => $request->harga_reg,
            'lokasi'        => $request->lokasi,
            'seats'         => $request->seats,
            'thumbnail'     => $posterPath,
            'kapasitas_vip' => $request->kapasitas_vip,
            'kapasitas_reg' => $request->kapasitas_reg,
            'kategori'      => $request->kategori,
            'status'        => 'open',
        ];

        // Simpan ke database melalui Model Event
        $event = Event::create($eventData);

        // 6. Response Sukses (Memperbaiki kode status 21 -> 201)
        return response()->json([
            'success' => true,
            'message' => 'Event berhasil dipublikasikan!',
            'data'    => $event
        ], 201);
    }

    public function show($id) 
    {
        $event = Event::with('organizer:id,user_id,nama_eo,status')->find($id);

        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Event tidak ditemukan.'
            ], 404);
        }

        if ($event->status !== 'open') {
            return response()->json([
                'success' => false,
                'message' => 'Event telah ditutup.'
            ], 404);
        }

        if ($event->thumbnail) {
            $event->poster_url = asset('storage/' . $event->thumbnail);
        } else {
            $event->poster_url = 'https://via.placeholder.com/600x400';
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail event berhasil diambil.',
            'data'    => $event
        ], 200);
    }
}