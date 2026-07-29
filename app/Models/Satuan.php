<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Satuan extends Model
{
    protected $table = 'satuans';

    protected $fillable = [
        'nama',
    ];

    protected $appends = ['tipe'];

    /**
     * Daftar kata kunci satuan yang sifatnya hitungan (nggak boleh koma).
     */
    private static array $satuanBulat = [
        'pcs', 'piece', 'buah', 'butir', 'lembar', 'bungkus',
        'pack', 'pak', 'dus', 'box', 'botol', 'biji', 'keping', 'unit',
    ];

    /**
     * Menebak tipe satuan (bulat/desimal) berdasarkan nama.
     */
    public static function tebakTipe(string $nama): string
    {
        $namaLower = strtolower(trim($nama));

        foreach (self::$satuanBulat as $kata) {
            if (str_contains($namaLower, $kata)) {
                return 'bulat';
            }
        }

        return 'desimal';
    }

    public function getTipeAttribute(): string
    {
        return self::tebakTipe($this->nama);
    }

    public function bahanBakus()
    {
        return $this->hasMany(BahanBaku::class, 'satuan_id');
    }
}