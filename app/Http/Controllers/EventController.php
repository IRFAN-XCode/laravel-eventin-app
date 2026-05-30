<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;

class EventController extends Controller
{
    
    public function index(Request $request)
    {
        try {
            $kategori = $request->query('kategori');
            $search = $request->query('search');

            $eventQuery = \App\Models\Event::with('organizer:id,nama_eo') 
                ->select('id', 'nama_event', 'tgl_event', 'harga_reg', 'organizer_id', 'thumbnail', 'kategori', 'status')
                ->where('status', 'open'); 

            if ($request->filled('kategori')) {
                $eventQuery->where('kategori', $kategori);
            }
            
            if ($request->filled('search')) {
                $searchKey = strtolower($request->query('search'));
                $eventQuery->whereRaw('LOWER(nama_event) LIKE ?', ["%{$searchKey}%"]);
            }

            $events = $eventQuery->orderBy('tgl_event', 'asc')->get();

            $events->map(function ($event) {
                if ($event->thumbnail) {
                    $event->poster_url = asset('storage/events/' . $event->thumbnail);
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

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan internal server.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function show($id) 
    {
            $event = \App\Models\Event::with(['transactions' => function($query) {
            // Hanya ambil transaksi yang nomor kursinya ada dan statusnya valid
            $query->whereNotNull('nomor_kursi')
                  ->where('nomor_kursi', '!=', '')
                  ->whereIn('status_pembayaran', ['checking_admin', 'success']);
        }])->find($id);

        if (!$event) {
            return response()->json(['success' => false, 'message' => 'Event tidak ditemukan'], 404);
        }

        $event->total_kapasitas = $event->kapasitas_vip + $event->kapasitas_reg;
        $event->poster_url = asset('storage/events/' . $event->thumbnail); 

        return response()->json([
            'success' => true,
            'data' => $event
        ], 200);
    }
}