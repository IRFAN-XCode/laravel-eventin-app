<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_transaksi',
        'user_id',
        'event_id',
        'jenis_tiket',
        'nomor_kursi',
        'jumlah_tiket',
        'total_harga',
        'status_pembayaran',
        'status_kehadiran',
        'payment_url'
    ];

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id', 'id');
    }
}
