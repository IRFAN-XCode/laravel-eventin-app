<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Event extends Model
{
    use HasFactory;

    protected $table = 'events';

    // perbarui oleh romi (1)
    protected $fillable = [
        'nama_event',
        'tgl_event',
        'organizer_id',
        'waktu',
        'harga_vip',
        'harga_reg',
        'lokasi',
        'seats',
        'thumbnail',
        'kapasitas_vip',
        'kapasitas_reg',
        'kategori',
        'status',
    ];

    public function organizer()
    {
        // 'App\Models\User' disesuaikan dengan model EO/User kamu
        return $this->belongsTo(Organizer::class, 'organizer_id'); 
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'event_id', 'id');
    }
}
