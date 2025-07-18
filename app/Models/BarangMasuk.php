<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarangMasuk extends Model
{
    /** @use HasFactory<\Database\Factories\BarangMasukFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    // ubah tanggal_expired menjadi date

    /**
     * Get the barang that belongs to this BarangMasuk.
     */
    public function barang()
    {
        return $this->hasMany(Barang::class, 'barang_masuk_id');
    }


    /**
     * Get the user that created this BarangMasuk.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    /**
     * Get the subKategori that belongs to this BarangMasuk.
     */
    public function subKategori()
    {
        return $this->belongsTo(SubKategori::class, 'sub_kategori_id');
    }
}
