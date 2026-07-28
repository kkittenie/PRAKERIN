<?php

namespace Database\Seeders;

use App\Models\Satuan;
use Illuminate\Database\Seeder;

class SatuanSeeder extends Seeder
{
    public function run(): void
    {
        $data = [

            'Kg',
            'Gram',
            'Liter',
            'Ml',
            'Pcs',
            'Pack',
            'Box',
            'Botol',
            'Lembar',
            'Buah'

        ];

        foreach ($data as $nama) {

            Satuan::firstOrCreate([
                'nama'=>$nama
            ]);

        }
    }
}