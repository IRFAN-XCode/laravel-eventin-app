<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrganizerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('organizer')->insert([
            [
                'nama' => 'Federico Barba',
                'email' => 'barba93@gmail.com',
                'nama_eo' => 'YTTA Vendor',
                'file_proposal' => 'proposal_jazz_malam.pdf',
                'role' => 'organizer',
                'created_at' => now(), 
                'updated_at' => now(),
            ],
            [
                'nama' => 'Maulana Haye',
                'email' => 'haye33@gmail.com',
                'nama_eo' => 'Gass aja',
                'file_proposal' => 'proposal_seminar.pdf',
                'role' => 'organizer',
                'created_at' => now(), 
                'updated_at' => now(),
            ],
            [
                'nama' => 'Azizi Asadel',
                'email' => 'zee@gmail.com',
                'nama_eo' => 'Zeemotin Club',
                'file_proposal' => 'proposal_workshop.pdf',
                'role' => 'organizer',
                'created_at' => now(), 
                'updated_at' => now(),
            ]
        ]);
    }
}
