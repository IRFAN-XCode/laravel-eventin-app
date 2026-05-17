<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Event extends Model
{
    use HasFactory;

    protected $table = 'list_event';

    protected $fillable = [
        'nama_event',
        'tgl_event',
        'harga_tiket',
        'organizer_id',
        'status',
    ];

    public function organizer()
    {
        // 'App\Models\User' disesuaikan dengan model EO/User kamu
        // 'organizer_id' adalah nama kolom foreign key di tabel list_event
        return $this->belongsTo(Organizer::class, 'organizer_id'); 
    }
}
