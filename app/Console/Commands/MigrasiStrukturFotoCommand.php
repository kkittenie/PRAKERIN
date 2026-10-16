<?php

namespace App\Console\Commands;

use App\Models\Banner;
use App\Models\Bundling;
use App\Models\DetailProduk;
use App\Models\Member;
use App\Models\Produk;
use App\Models\QrisImage;
use App\Models\RewardKatalog;
use App\Models\Transaksi;
use App\Models\WelcomeSlide;
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

        // 7. QRIS
        foreach (QrisImage::whereNotNull('gambar')->where('gambar', '!=', '')->get() as $qris) {
            $oldPath = $qris->gambar;
            $kode = $qris->kode_qris;

            $targetPrefix = "qris/{$kode}/";
            if (str_starts_with($oldPath, $targetPrefix)) {
                continue;
            }

            if (Storage::disk('public')->exists($oldPath)) {
                $newPath = $targetPrefix . basename($oldPath);

                Storage::disk('public')->move($oldPath, $newPath);
                $qris->update(['gambar' => $newPath, 'kode_qris' => $kode]);
                $this->line("QRIS [{$kode}]: {$oldPath} -> {$newPath}");
                $totalMigrated++;
            }
        }

        // 8. Welcome Slide
        foreach (WelcomeSlide::whereNotNull('gambar')->where('gambar', '!=', '')->get() as $slide) {
            $oldPath = $slide->gambar;
            $kode = $slide->kode_slide;

            $targetPrefix = "welcome-slide/{$kode}/";
            if (str_starts_with($oldPath, $targetPrefix)) {
                continue;
            }

            if (Storage::disk('public')->exists($oldPath)) {
                $newPath = $targetPrefix . basename($oldPath);

                Storage::disk('public')->move($oldPath, $newPath);
                $slide->update(['gambar' => $newPath, 'kode_slide' => $kode]);
                $this->line("Welcome Slide [{$kode}]: {$oldPath} -> {$newPath}");
                $totalMigrated++;
            }
        }

        // 9. Foto detail produk (varian/rasa) -> varian-rasa/{kode_produk}/
        // Pakai COPY (bukan move): file lama di varian-rasa/ dulu dipakai bersama oleh
        // beberapa detail sekaligus. File lama yang sudah tidak dipakai siapa pun
        // dibersihkan oleh langkah pembersihan di bawah.
        foreach (DetailProduk::whereNotNull('foto')->where('foto', '!=', '')->get() as $detail) {
            $oldPath = $detail->foto;
            $kode = $detail->kode_produk;
            if (!$kode || str_starts_with($oldPath, 'http://') || str_starts_with($oldPath, 'https://')) {
                continue;
            }

            $folderKode = trim(preg_replace('/[^A-Za-z0-9_-]+/', '-', $kode), '-');
            $targetPrefix = "varian-rasa/{$folderKode}/";
            if (str_starts_with($oldPath, $targetPrefix)) {
                continue;
            }

            if (Storage::disk('public')->exists($oldPath)) {
                $newPath = $targetPrefix . basename($oldPath);

                Storage::disk('public')->copy($oldPath, $newPath);
                $detail->update(['foto' => $newPath]);
                $this->line("Detail Produk [{$kode}]: {$oldPath} -> {$newPath}");
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
            ->merge(QrisImage::whereNotNull('gambar')->where('gambar', '!=', '')->pluck('gambar'))
            ->merge(WelcomeSlide::whereNotNull('gambar')->where('gambar', '!=', '')->pluck('gambar'))
            ->merge(DetailProduk::whereNotNull('foto')->where('foto', '!=', '')->pluck('foto'))
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