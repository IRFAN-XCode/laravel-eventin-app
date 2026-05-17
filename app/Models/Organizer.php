<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organizer extends Model
{
    use HasFactory;

    // Spesifikkan nama tabel sesuai migration
    protected $table = 'organizer';

    // Kolom-kolom yang ada di migration kamu
    protected $fillable = [
        'nama',
        'email',
        'nama_eo',
        'file_proposal',
        'status',
        'role,'
    ];
}
