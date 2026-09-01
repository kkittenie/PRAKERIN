<?php

namespace Database\Seeders;

use App\Models\Produk;
use App\Models\StampProgram;
use App\Models\StampProgramHadiah;
use Illuminate\Database\Seeder;

class StampProgramSeeder extends Seeder
{
    public function run(): void
    {
        $program = StampProgram::firstOrCreate(
            ['nama_program' => 'Kumpulkan 5 Stamp, Gratis Pastry!'],
            [
                'deskripsi' => 'Setiap belanja minimal Rp25.000 dapat 1 stamp. Kumpulkan 5 stamp dan tukar dengan pastry favorit kamu, gratis!',
                'minimal_pembelian' => 25000,
                'jumlah_stamp_dibutuhkan' => 5,
                'maksimal_redeem' => null,
                'jumlah_redeem' => 0,
                'tanggal_mulai' => now()->startOfMonth()->toDateString(),
                'tanggal_berakhir' => null,
                'status_aktif' => false,
            ]
        );

        $hadiahList = [
            ['nama_produk' => 'Butter Croissant', 'jumlah' => 1],
            ['nama_produk' => 'Soft Milk Bun', 'jumlah' => 1],
            ['nama_produk' => 'Chocolate Chip Cookie', 'jumlah' => 2],
        ];

        foreach ($hadiahList as $item) {
            $produk = Produk::where('nama_produk', $item['nama_produk'])->first();

            if (! $produk) {
                continue;
            }

            StampProgramHadiah::firstOrCreate(
                [
                    'stamp_program_id' => $program->id,
                    'produk_id' => $produk->id,
                ],
                [
                    'jumlah' => $item['jumlah'],
                ]
            );
        }
    }
}