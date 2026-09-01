<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $table = 'banners';

    protected $fillable = [
        'kode_banner',
        'judul',
        'gambar',
        'urutan',
        'status_aktif',
        'tanggal_mulai_tampil',
        'tanggal_selesai_tampil',
    ];

    protected $casts = [
        'status_aktif' => 'boolean',
        'tanggal_mulai_tampil' => 'date',
        'tanggal_selesai_tampil' => 'date',
    ];

    // Accessor untuk kode banner
    public function getKodeBannerAttribute($value): string
    {
        if (!empty($value)) {
            return $value;
        }
        return 'BNR' . str_pad((string) $this->id, 3, '0', STR_PAD_LEFT);
    }

    // Scope untuk ambil banner yang sedang aktif tampil (dipakai nanti di API carousel mobile)
    //
    // PENTING: tanggal_mulai_tampil & tanggal_selesai_tampil kolomnya
    // bertipe `date` (tanpa jam), jadi dibandinginnya harus ke tanggal hari
    // ini (toDateString()), BUKAN ke now() yang full datetime. Kalau
    // dibandingin ke now(), tanggal_selesai_tampil (tersimpan sbg
    // 00:00:00) bakal keanggep "lewat" begitu masuk jam berapa pun di hari
    // itu, jadi banner ilang dari carousel sehari lebih awal dari yang
    // di-set admin.
    public function scopeTampil($query)
    {
        $hariIni = now()->toDateString();
        return $query->where('status_aktif', true)
            ->where(function ($q) use ($hariIni) {
                $q->whereNull('tanggal_mulai_tampil')
                  ->orWhere('tanggal_mulai_tampil', '<=', $hariIni);
            })
            ->where(function ($q) use ($hariIni) {
                $q->whereNull('tanggal_selesai_tampil')
                  ->orWhere('tanggal_selesai_tampil', '>=', $hariIni);
            });
    }
}