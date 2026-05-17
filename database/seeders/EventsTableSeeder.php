<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('list_event')->insert([
            [
                'nama_event' => 'Konser Musik Senja',
                'nama_eo' => 'Senja Production',
                'tgl_event' => '2026-06-15',
                'harga_tiket' => 150000,
                'lokasi' => 'Std. Madya Jakarta',
                'kapasitas' => 2000,
                'kategori' => 'Musik',
                'status' => 'open',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_event' => 'Tech Conference 2026',
                'nama_eo' => 'Hello id',
                'tgl_event' => '2026-07-20',
                'harga_tiket' => 350000,
                'lokasi' => 'Gambir Expo',
                'kapasitas' => 2500,
                'kategori' => 'Workshop',
                'status' => 'open',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_event' => 'Festival Kuliner Nusantara',
                'nama_eo' => 'EventIn Aja',
                'tgl_event' => '2026-08-05',
                'harga_tiket' => 25000,
                'lokasi' => 'AEON Mall Deltamas',
                'kapasitas' => 2100,
                'kategori' => 'Workshop',
                'status' => 'open',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_event' => 'Seminar Investasi Muda',
                'nama_eo' => 'SiM Group',
                'tgl_event' => '2026-09-12',
                'harga_tiket' => 75000,
                'lokasi' => 'Sarinah Hall',
                'kapasitas' => 2400,
                'kategori' => 'Seminar',
                'status' => 'open',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_event' => 'Workshop UI/UX Design',
                'nama_eo' => 'Creative Agency',
                'tgl_event' => '2026-10-01',
                'harga_tiket' => 200000,
                'lokasi' => 'Jalarta Convention Hall',
                'kapasitas' => 2000,
                'kategori' => 'Workshop',
                'status' => 'open',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_event' => 'Konser Jazz Malam',
                'nama_eo' => 'Jazz Club',
                'tgl_event' => '2026-05-01',
                'harga_tiket' => 200000,
                'lokasi' => 'Istora Senayan',
                'kapasitas' => 1500,
                'kategori' => 'Musik',
                'status' => 'open',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
