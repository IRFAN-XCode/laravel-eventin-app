<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Event;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    /**
     * Menampilkan semua data event (Untuk Request GET)
     */
    public function index()
{
    // Menggunakan Eloquent untuk filter, pilih kolom tertentu, dan ambil nama EO
    $events = Event::with('organizer:id,nama_eo') 
        ->select('id', 'nama_event', 'tgl_event', 'harga_tiket', 'organizer_id')
        ->where('status', 'open') 
        ->get();

    // Menggunakan format response milikmu yang rapi
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
        // 1. Validasi inputan dari Postman
        $validator = Validator::make($request->all(), [
            'event_name'  => 'required|string|max:255',
            'email_eo'    => 'required|email|unique:list_event,email_eo',
            'tgl_event'   => 'required|date',
            'harga_tiket' => 'required|integer|min:0',
        ]);

        // 2. Jika validasi gagal, kirim error status 422
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors()
            ], 422);
        }

        // 3. Jika validasi lolos, insert data ke database
        $data = [
            'event_name'  => $request->event_name,
            'email_eo'    => $request->email_eo,
            'tgl_event'   => $request->tgl_event,
            'harga_tiket' => $request->harga_tiket,
            'created_at'  => now(),
            'updated_at'  => now(),
        ];

        DB::table('list_event')->insert($data);

        // 4. Kirim respon sukses status 201
        return response()->json([
            'success' => true,
            'message' => 'Event baru berhasil ditambahkan!',
            'data'    => $data
        ], 201);
    }

//     public function update(Request $request, string $id)
// {
// $event = name_event::findOrFail($id);
// $request->validate([
// 'name' => 'required|string|max:255',
// 'email' => 'required|email|unique:users,email,' . $event->id,
// 'tgl_event'   => 'required|date',
//             'harga_tiket' => 'required|integer|min:0',
// // 'role' => 'required|in:admin,user'
// ]);
// // Update the user details
// $user->name = $request->name;
// $user->email = $request->email;

// $user->role = $request->role;
// $user->save();
// return redirect()->route('user.index')->with('success', 'Data pengguna
// berhasil diubah');
}