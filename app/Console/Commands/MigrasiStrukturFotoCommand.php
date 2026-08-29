<?php

namespace App\Console\Commands;

use App\Models\Banner;
use App\Models\Bundling;
use App\Models\Member;
use App\Models\Produk;
use App\Models\RewardKatalog;
use App\Models\Transaksi;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrasiStrukturFotoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrasi-struktur-foto';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Memindahkan foto/gambar lama di storage public ke folder berdasar kode data masing-masing dan mengupdate database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai migrasi struktur folder foto...');
        $totalMigrated = 0;

        // 1. Produk
        $produks = Produk::whereNotNull('foto')->where('foto', '!=', '')->get();
        foreach ($produks as $produk) {
            $oldPath = $produk->foto;
            $kode = $produk->kode_produk;
            if (!$kode) {
                continue;
            }

            $targetPrefix = "produk/{$kode}/";
            if (str_starts_with($oldPath, $targetPrefix)) {
                continue;
            }

            if (Storage::disk('public')->exists($oldPath)) {
                $filename = basename($oldPath);
                $newPath = $targetPrefix . $filename;

                Storage::disk('public')->move($oldPath, $newPath);
                $produk->update(['foto' => $newPath]);
                $this->line("Produk [{$kode}]: {$oldPath} -> {$newPath}");
                $totalMigrated++;
            }
        }

        // 2. Member
        $members = Member::whereNotNull('foto')->where('foto', '!=', '')->get();
        foreach ($members as $member) {
            $oldPath = $member->foto;
            $kode = $member->kode_member ?: Member::generateKodeMember($member->id);
            if (!$kode) {
                continue;
            }

            $targetPrefix = "member/{$kode}/";
            if (str_starts_with($oldPath, $targetPrefix)) {
                continue;
            }

            if (Storage::disk('public')->exists($oldPath)) {
                $filename = basename($oldPath);
                $newPath = $targetPrefix . $filename;

                Storage::disk('public')->move($oldPath, $newPath);
                $member->update(['foto' => $newPath, 'kode_member' => $kode]);
                $this->line("Member [{$kode}]: {$oldPath} -> {$newPath}");
                $totalMigrated++;
            }
        }

        // 3. Bundling
        $bundlings = Bundling::whereNotNull('gambar')->where('gambar', '!=', '')->get();
        foreach ($bundlings as $bundling) {
            $oldPath = $bundling->gambar;
            $kode = $bundling->kode_bundling ?: ('BDL' . str_pad((string) $bundling->id, 4, '0', STR_PAD_LEFT));

            $targetPrefix = "bundling/{$kode}/";
            if (str_starts_with($oldPath, $targetPrefix)) {
                continue;
            }

            if (Storage::disk('public')->exists($oldPath)) {
                $filename = basename($oldPath);
                $newPath = $targetPrefix . $filename;

                Storage::disk('public')->move($oldPath, $newPath);
                $bundling->update(['gambar' => $newPath, 'kode_bundling' => $kode]);
                $this->line("Bundling [{$kode}]: {$oldPath} -> {$newPath}");
                $totalMigrated++;
            }
        }

        // 4. Banner
        $banners = Banner::whereNotNull('gambar')->where('gambar', '!=', '')->get();
        foreach ($banners as $banner) {
            $oldPath = $banner->gambar;
            $kode = $banner->kode_banner ?: ('BNR' . str_pad((string) $banner->id, 3, '0', STR_PAD_LEFT));

            $targetPrefix = "banner/{$kode}/";
            if (str_starts_with($oldPath, $targetPrefix)) {
                continue;
            }

            if (Storage::disk('public')->exists($oldPath)) {
                $filename = basename($oldPath);
                $newPath = $targetPrefix . $filename;

                Storage::disk('public')->move($oldPath, $newPath);
                $banner->update(['gambar' => $newPath, 'kode_banner' => $kode]);
                $this->line("Banner [{$kode}]: {$oldPath} -> {$newPath}");
                $totalMigrated++;
            }
        }

        // 5. Bukti Pembayaran Transaksi
        $transaksis = Transaksi::whereNotNull('bukti_pembayaran')->where('bukti_pembayaran', '!=', '')->get();
        foreach ($transaksis as $transaksi) {
            $oldPath = $transaksi->bukti_pembayaran;
            $kode = $transaksi->kode_transaksi;
            if (!$kode) {
                continue;
            }

            $targetPrefix = "bukti-pembayaran/{$kode}/";
            if (str_starts_with($oldPath, $targetPrefix)) {
                continue;
            }

            if (Storage::disk('public')->exists($oldPath)) {
                $filename = basename($oldPath);
                $newPath = $targetPrefix . $filename;

                Storage::disk('public')->move($oldPath, $newPath);
                $transaksi->update(['bukti_pembayaran' => $newPath]);
                $this->line("Transaksi [{$kode}]: {$oldPath} -> {$newPath}");
                $totalMigrated++;
            }
        }

        // 6. Reward Katalog
        $rewards = RewardKatalog::whereNotNull('gambar')->where('gambar', '!=', '')->get();
        foreach ($rewards as $reward) {
            $oldPath = $reward->gambar;
            $kode = $reward->kode_reward ?: ('RWD' . str_pad((string) $reward->id, 3, '0', STR_PAD_LEFT));

            $targetPrefix = "reward/{$kode}/";
            if (str_starts_with($oldPath, $targetPrefix)) {
                continue;
            }

            if (Storage::disk('public')->exists($oldPath)) {
                $filename = basename($oldPath);
                $newPath = $targetPrefix . $filename;

                Storage::disk('public')->move($oldPath, $newPath);
                $reward->update(['gambar' => $newPath, 'kode_reward' => $kode]);
                $this->line("Reward [{$kode}]: {$oldPath} -> {$newPath}");
                $totalMigrated++;
            }
        }

        $this->info("Migrasi selesai! Total {$totalMigrated} file foto berhasil dipindahkan.");

        // Pembersihan file tidak terpakai (orphaned files)
        $this->info('Memeriksa file sampah / tidak terpakai di storage...');
        $activePaths = collect()
            ->merge(Banner::whereNotNull('gambar')->where('gambar', '!=', '')->pluck('gambar'))
            ->merge(Member::whereNotNull('foto')->where('foto', '!=', '')->pluck('foto'))
            ->merge(Bundling::whereNotNull('gambar')->where('gambar', '!=', '')->pluck('gambar'))
            ->merge(Produk::whereNotNull('foto')->where('foto', '!=', '')->pluck('foto'))
            ->merge(RewardKatalog::whereNotNull('gambar')->where('gambar', '!=', '')->pluck('gambar'))
            ->merge(Transaksi::whereNotNull('bukti_pembayaran')->where('bukti_pembayaran', '!=', '')->pluck('bukti_pembayaran'))
            ->filter()
            ->unique()
            ->toArray();

        $disk = Storage::disk('public');
        $deletedCount = 0;

        foreach ($disk->allFiles() as $file) {
            if (basename($file) === '.gitignore') {
                continue;
            }

            if (basename($file) === '.DS_Store' || !in_array($file, $activePaths)) {
                $disk->delete($file);
                $this->line("Dihapus (file tidak terpakai): {$file}");
                $deletedCount++;
            }
        }

        // Hapus folder kosong lama jika ada (misal produk/images, member-foto)
        $oldFolders = ['produk/images', 'member-foto', 'banner/images'];
        foreach ($oldFolders as $oldFolder) {
            if ($disk->exists($oldFolder) && empty($disk->allFiles($oldFolder))) {
                $disk->deleteDirectory($oldFolder);
                $this->line("Folder kosong dihapus: {$oldFolder}");
            }
        }

        $this->info("Pembersihan selesai! Total {$deletedCount} file sampah telah dihapus.");

        return Command::SUCCESS;
    }
}
