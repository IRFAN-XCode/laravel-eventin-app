<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Organizer;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;


class ProfileController extends Controller
{
    public function getProfile(Request $request) 
    {
        $authUser = $request->attributes->get('auth_user');

        if (!$authUser) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna tidak ditemukan.'
            ], 401);
        }

        $user = User::find($authUser->id);

        if(!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Data Pengguna tidak dapat ditemukan.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'data profile berhasil diambil.',
            'data' => [
                'id' => $user->id,
                'nama' => $user->nama,
                'email' => $user->email,
                'nomor_handphone' => $user->nomor_handphone,
                'jenis_kelamin' => $user->jenis_kelamin,
                'role' => $user->role
            ]
        ], 200);
    }

    public function updateProfile(Request $request)
{
    $authUser = $request->attributes->get('auth_user');

    if (!$authUser) {
        return response()->json([
            'success' => false,
            'message' => 'Sesi tidak valid atau pengguna tidak ditemukan.'
        ], 401);
    }

    // Cari data user asli di database agar bisa memanggil ->update()
    $user = User::find($authUser->id);

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'Pengguna tidak ditemukan di database.'
        ], 404);
    }
    
    $validator = Validator::make($request->all(), [
        'nama' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $user->id,
        'nomor_handphone' => 'required|string|max:20',
        'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
    ], [
        'nama.required' => 'Nama lengkap wajib diisi.',
        'email.required' => 'Email wajib diisi.',
        'email.unique' => 'Email sudah terdaftar pada akun lain.',
        'nomor_handphone.required' => 'Nomor handphone wajib diisi.',
        'jenis_kelamin.required' => 'Jenis kelamin wajib diisi.',
        'jenis_kelamin.in' => 'Pilihan jenis kelamin tidak valid.'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false, 
            'message' => 'Validasi gagal',
            'errors' => $validator->errors()
        ], 422);
    }

    // Update seluruh field yang dikirimkan dari aplikasi mobile
    $user->update($request->only('nama', 'email', 'nomor_handphone', 'jenis_kelamin'));

    // Return data yang sudah di-update
    return response()->json([
        'success' => true,
        'message' => 'Profil berhasil diperbarui.',
        'data' => [
            'id' => $user->id,
            'nama' => $user->nama,
            'email' => $user->email,
            'nomor_handphone' => $user->nomor_handphone,
            'jenis_kelamin' => $user->jenis_kelamin,
            'role' => $user->role
        ]
    ], 200);
}

public function deleteAccount(Request $request)
{
    $authUser = $request->attributes->get('auth_user');

    if (!$authUser) {
        return response()->json([
            'success' => false,
            'message' => 'Sesi tidak valid atau pengguna tidak ditemukan.'
        ], 401);
    }

    $user = User::find($authUser->id);

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'Pengguna tidak ditemukan di database.'
        ], 404);
    }

    DB::beginTransaction();


try {
    if ($user->role === 'organizer') {
        
        // Ambil data organizer berdasarkan user_id sebelum dihapus
        $organizer = Organizer::where('user_id', $user->id)->first();

        if ($organizer) {
            if ($organizer->file_proposal && Storage::disk('public')->exists($organizer->file_proposal)) {
                
                Storage::disk('public')->delete($organizer->file_proposal);
            }

            // Hapus baris data dari tabel 'organizers'
            $organizer->delete();
        }
    }

    $user->delete();

    DB::commit();

    return response()->json([
        'success' => true,
        'message' => 'Akun Anda beserta berkas proposal berhasil dihapus secara permanen.'
    ], 200);

} catch (\Exception $e) {
    DB::rollback();

    return response()->json([
        'success' => false,
        'message' => 'Gagal menghapus akun. Terjadi kesalahan pada server.',
        'error' => $e->getMessage()
    ], 500);
}
}
}
