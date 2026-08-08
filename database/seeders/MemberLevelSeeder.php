<?php

namespace Database\Seeders;

use App\Models\MemberLevel;
use Illuminate\Database\Seeder;

class MemberLevelSeeder extends Seeder
{
    public function run(): void
    {
        $levels = [
            ['nama_level' => 'Bronze', 'minimal_poin' => 0, 'warna_badge' => 'orange'],
            ['nama_level' => 'Silver', 'minimal_poin' => 300, 'warna_badge' => 'gray'],
            ['nama_level' => 'Gold', 'minimal_poin' => 500, 'warna_badge' => 'yellow'],
        ];

        foreach ($levels as $level) {
            MemberLevel::firstOrCreate(
                ['nama_level' => $level['nama_level']],
                $level + ['status_aktif' => true]
            );
        }
    }
}