<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
}
