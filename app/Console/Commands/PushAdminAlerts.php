<?php

namespace App\Console\Commands;

use App\Models\BahanBaku;
use App\Models\Produk;
use App\Models\Produksi;
use App\Support\NotificationHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class PushAdminAlerts extends Command
{
    protected $signature = 'notifikasi:push-admin';

    protected $description = 'Kirim push notification ke admin untuk alert sistem yang baru muncul (stok menipis, produk kadaluarsa).';

    public function handle(): int
    {
        foreach ($this->kumpulkanAlert() as $kunci => $alert) {
            $cacheKey = 'admin_push_alert:' . $kunci;

            $sebelumnya = array_map('strval', Cache::get($cacheKey, []));
            $sekarang = array_map('strval', array_keys($alert['items']));

            // Cuma push item yang BARU masuk kondisi alert. Item yang masih
            // menipis dari sebelumnya nggak di-push ulang tiap kali command jalan.
            $baru = array_values(array_diff($sekarang, $sebelumnya));

            if (! empty($baru)) {
                $nama = array_map(fn ($id) => '"' . $alert['items'][$id] . '"', $baru);
                $tampil = array_slice($nama, 0, 3);
                $sisa = count($nama) - count($tampil);

                $ringkasan = implode(', ', $tampil) . ($sisa > 0 ? " dan {$sisa} lainnya" : '') . ' ' . $alert['kalimat'];

                NotificationHelper::pushKeAdmin(
                    $alert['judul'],
                    $ringkasan,
                    $alert['url'],
                    'sistem',
                    $alert['permission']
                );
            }

            // Simpan kondisi terbaru. Kalau stok sudah diisi lalu menipis lagi
            // nanti, item itu otomatis dianggap "baru" dan di-push lagi.
            Cache::forever($cacheKey, $sekarang);
        }

        return self::SUCCESS;
    }

    /**
     * @return array<string, array{judul: string, kalimat: string, url: string, permission: string, items: array<int|string, string>}>
     */
    private function kumpulkanAlert(): array
    {
        $produkMenipis = Produk::with('detailProduks')
            ->where('status_aktif', true)
            ->stokMenipis()
            ->get()
            ->mapWithKeys(fn ($p) => [$p->id => $p->nama_produk])
            ->all();

        $bahanMenipis = BahanBaku::whereColumn('stok', '<=', 'stok_minimum')
            ->get()
            ->mapWithKeys(fn ($b) => [$b->id => $b->nama_bahan])
            ->all();

        $produksiKadaluarsa = Produksi::with('produk')
            ->whereColumn('jumlah_disesuaikan', '<', 'jumlah_hasil')
            ->get()
            ->filter(fn ($item) => $item->is_kadaluarsa)
            ->mapWithKeys(fn ($item) => [$item->id => optional($item->produk)->nama_produk ?? ('Batch #' . $item->id)])
            ->all();

        return [
            'produk_menipis' => [
                'judul' => 'Stok Produk Menipis',
                'kalimat' => 'stoknya menipis.',
                'url' => route('produk.index'),
                'permission' => 'lihat_produk',
                'items' => $produkMenipis,
            ],
            'bahan_menipis' => [
                'judul' => 'Bahan Baku Menipis',
                'kalimat' => 'stoknya menipis.',
                'url' => route('bahan-baku.index'),
                'permission' => 'lihat_stok',
                'items' => $bahanMenipis,
            ],
            'produksi_kadaluarsa' => [
                'judul' => 'Produk Kadaluwarsa',
                'kalimat' => 'sudah kadaluarsa.',
                'url' => route('produksi.index'),
                'permission' => 'lihat_produksi',
                'items' => $produksiKadaluarsa,
            ],
        ];
    }
}