<?php

namespace App\Support;

use App\Models\AppNotification;
use App\Models\BahanBaku;
use App\Models\MemberDeviceToken;
use App\Models\PesananDitahan;
use App\Models\Produk;
use App\Models\Produksi;
use App\Models\Satuan;
use App\Models\Transaksi;

class NotificationHelper
{
    /**
     * Dapatkan semua notifikasi (gabungan database + alert realtime sistem).
     */
    public static function getLiveNotifications(int $limit = 20): array
    {
        $notifications = [];
        $readKeys = array_map('strval', session()->get('read_notifications', []));

        // 1. Notifikasi dari database
        // Sejak app_notifications dipakai bareng buat notif member (lihat migrasi
        // add_member_notif_support), wajib exclude yang punya member_id supaya
        // notifikasi pribadi member (mis. "Pesanan kamu sudah kami terima" dgn
        // url /mobile/pesanan/{id}) nggak nyasar ke dropdown dashboard admin.
        $dbNotifications = AppNotification::whereNull('member_id')
            ->latest()
            ->limit($limit)
            ->get();

        foreach ($dbNotifications as $notif) {
            $notifications[] = [
                'id' => $notif->id,
                'is_db' => true,
                'type' => $notif->type,
                'title' => $notif->title,
                'desc' => $notif->desc,
                'time' => $notif->created_at->diffForHumans(),
                'raw_time' => $notif->created_at->timestamp,
                'is_read' => $notif->is_read || in_array((string) $notif->id, $readKeys),
                'icon' => static::getIconHtml($notif->icon_type),
                'color' => static::getColorClass($notif->icon_type),
                'url' => $notif->url ?? '#',
            ];
        }

        // 2. Alert sistem: Pesanan Pickup Baru (Masuk dari Member App)
        $pesananBaru = Transaksi::where('asal_pesanan', Transaksi::ASAL_MEMBER_APP)
            ->whereIn('status_pembayaran', ['Menunggu Pembayaran', 'Belum Lunas', 'Menunggu Konfirmasi'])
            ->latest()
            ->limit(5)
            ->get();

        foreach ($pesananBaru as $pesanan) {
            $idKey = 'pickup_' . $pesanan->id;

            // status_pesanan di DB langsung ke-set 'diproses' begitu pesanan
            // dibuat (lihat CheckoutController::store), jadi jangan dipakai
            // mentah-mentah buat teks notif. Status yang beneran nunjukin
            // kasir udah mulai proses adalah diproses_pada (masih null =
            // pesanan baru masuk & belum disentuh sama sekali).
            $statusLabel = $pesanan->diproses_pada
                ? 'sedang diproses'
                : 'menunggu diproses';

            $notifications[] = [
                'id' => $idKey,
                'is_db' => false,
                'type' => 'Pesanan Pickup Baru',
                'title' => 'Pesanan Pickup #' . $pesanan->kode_transaksi,
                'desc' => 'Pesanan pickup baru dari member. Status: ' . $statusLabel,
                'time' => $pesanan->created_at->diffForHumans(),
                'raw_time' => $pesanan->created_at->timestamp,
                'is_read' => in_array($idKey, $readKeys),
                'icon' => static::getIconHtml('success'),
                'color' => static::getColorClass('success'),
                // route('pesanan-pickup.detail', ...) itu endpoint JSON buat
                // di-fetch() JS pengisi modal (lihatDetail()), BUKAN halaman.
                // Kalau dipakai langsung sbg href, browser navigasi ke situ
                // dan cuma nampilin raw JSON. Arahkan ke halaman index-nya,
                // dibawain query 'buka' biar modal detail auto kebuka.
                'url' => route('pesanan-pickup.indeks', ['buka' => $pesanan->id]),
            ];
        }

        // 4. Alert sistem: Stok Produk Menipis
        $lowStockProduk = Produk::with('detailProduks')->where('status_aktif', true)->stokMenipis()->get();
        foreach ($lowStockProduk as $produk) {
            $idKey = 'produk_' . $produk->id;
            $notifications[] = [
                'id' => $idKey,
                'is_db' => false,
                'type' => 'Stok Produk Menipis',
                'title' => 'Stok Produk Menipis',
                'desc' => 'Produk "' . $produk->nama_produk . '" sisa ' . $produk->stok . ' pcs (minimum ' . $produk->stok_minimum . ' pcs).',
                'time' => $produk->updated_at->diffForHumans(),
                'raw_time' => $produk->updated_at->timestamp,
                'is_read' => in_array($idKey, $readKeys),
                'icon' => static::getIconHtml('amber'),
                'color' => static::getColorClass('amber'),
                'url' => route('produk.index'),
            ];
        }

        // 5. Alert sistem: Stok Bahan Menipis
        $lowStockBahan = BahanBaku::whereColumn('stok', '<=', 'stok_minimum')->get();
        foreach ($lowStockBahan as $bahan) {
            $idKey = 'bahan_' . $bahan->id;
            $namaSatuan = optional($bahan->satuan)->nama ?? '-';
            $notifications[] = [
                'id' => $idKey,
                'is_db' => false,
                'type' => 'Stok Bahan Menipis',
                'title' => 'Bahan Baku Menipis',
                'desc' => 'Bahan "' . $bahan->nama_bahan . '" sisa ' . Satuan::formatAngka($bahan->stok, optional($bahan->satuan)->tipe) . ' ' . $namaSatuan . ' (minimum ' . Satuan::formatAngka($bahan->stok_minimum, optional($bahan->satuan)->tipe) . ' ' . $namaSatuan . ').',
                'time' => $bahan->updated_at->diffForHumans(),
                'raw_time' => $bahan->updated_at->timestamp,
                'is_read' => in_array($idKey, $readKeys),
                'icon' => static::getIconHtml('error'),
                'color' => static::getColorClass('error'),
                'url' => route('bahan-baku.index'),
            ];
        }

        // 6. Alert sistem: Produksi Kadaluarsa
        $produksiKadaluarsa = Produksi::with('produk')
            ->whereColumn('jumlah_disesuaikan', '<', 'jumlah_hasil')
            ->get()
            ->filter(fn ($item) => $item->is_kadaluarsa);

        foreach ($produksiKadaluarsa as $item) {
            $idKey = 'produksi_' . $item->id;
            $notifications[] = [
                'id' => $idKey,
                'is_db' => false,
                'type' => 'Produk Kadaluarsa',
                'title' => 'Produk Kadaluwarsa',
                'desc' => 'Batch produksi "' . optional($item->produk)->nama_produk . '" sisa ' . $item->sisa_belum_disesuaikan . ' pcs (dari ' . $item->jumlah_hasil . ' pcs) sudah kadaluarsa.',
                'time' => $item->created_at->diffForHumans(),
                'raw_time' => $item->created_at->timestamp,
                'is_read' => in_array($idKey, $readKeys),
                'icon' => static::getIconHtml('error'),
                'color' => static::getColorClass('error'),
                'url' => route('produksi.index'),
            ];
        }

        // Urutkan notifikasi terbaru di paling atas
        usort($notifications, fn ($a, $b) => $b['raw_time'] <=> $a['raw_time']);

        return array_slice($notifications, 0, $limit);
    }

    public static function create(string $title, string $desc, string $type = 'sistem', ?string $url = null, string $iconType = 'info'): AppNotification
    {
        return AppNotification::create([
            'title' => $title,
            'desc' => $desc,
            'type' => $type,
            'url' => $url,
            'icon_type' => $iconType,
            'is_read' => false,
        ]);
    }

    /**
     * Normalisasi URL agar selalu menggunakan host/port request yang sedang aktif
     * daripada menembak IP lama yang tersimpan di database.
     */
    public static function normalizeUrl(?string $url): string
    {
        if (empty($url)) {
            return route('member.notifikasi.index');
        }

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            $parsed = parse_url($url);
            $path = $parsed['path'] ?? '/';
            $query = isset($parsed['query']) ? '?' . $parsed['query'] : '';
            return url($path . $query);
        }

        return url($url);
    }

    /**
     * Ambil notifikasi in-app milik satu member (buat halaman
     * /mobile/notifikasi & badge unread di lonceng beranda).
     */
    public static function getNotifikasiMember(int $memberId, int $limit = 50)
    {
        $notifikasis = AppNotification::untukMember($memberId)
            ->latest()
            ->limit($limit)
            ->get();

        foreach ($notifikasis as $notif) {
            $notif->url = static::normalizeUrl($notif->url);
        }

        return $notifikasis;
    }

    public static function hitungBelumDibacaMember(int $memberId): int
    {
        return AppNotification::untukMember($memberId)->where('is_read', false)->count();
    }

    /**
     * Bikin notifikasi in-app buat member + kirim push notification (FCM)
     * ke HP-nya, biar kaya notif WA: tetap masuk walau aplikasinya ditutup.
     */
    public static function untukMember(int $memberId, string $title, string $desc, string $type = 'pesanan', ?string $url = null, string $iconType = 'info'): AppNotification
    {
        $notif = AppNotification::create([
            'member_id' => $memberId,
            'title' => $title,
            'desc' => $desc,
            'type' => $type,
            'url' => $url,
            'icon_type' => $iconType,
            'is_read' => false,
        ]);

        $tokens = MemberDeviceToken::where('member_id', $memberId)->pluck('token')->all();

        if (! empty($tokens)) {
            $tokensGagal = (new FcmService())->kirimKeTokens($tokens, $title, $desc, [
                'notification_id' => (string) $notif->id,
                'url' => $url ?? '',
                'type' => $type,
            ]);

            if (! empty($tokensGagal)) {
                MemberDeviceToken::whereIn('token', $tokensGagal)->delete();
            }
        }

        return $notif;
    }

    /**
     * Pesan notifikasi buat member berdasarkan aksi kasir di halaman
     * Pesanan Pickup. Dipanggil dari App\Listeners\KirimNotifikasiMemberPesanan
     * (yang listen ke event PesananPickupUpdated).
     *
     * @return array{0: string, 1: string, 2: string}|null  [title, desc, icon_type] atau null kalau aksi ini nggak perlu dinotifikasikan ke member
     */
    public static function pesanUntukAksiPesanan(Transaksi $transaksi, string $aksi): ?array
    {
        $kode = $transaksi->kode_transaksi;

        return match ($aksi) {
            'baru' => [
                'Pesanan diterima',
                "Pesanan {$kode} kamu sudah kami terima, selesaikan pembayaran ya.",
                'info',
            ],
            'lunas' => [
                'Pembayaran dikonfirmasi',
                "Pembayaran pesanan {$kode} sudah dikonfirmasi toko. Pesanan kamu segera diproses.",
                'success',
            ],
            'tolak_bukti' => [
                'Bukti pembayaran ditolak',
                ($transaksi->catatan_penolakan ?: 'Bukti pembayaran tidak valid') . ". Silakan upload ulang bukti pembayaran {$kode}.",
                'error',
            ],
            'diproses' => [
                'Pesanan sedang diproses',
                "Pesanan {$kode} kamu sedang disiapkan oleh toko.",
                'info',
            ],
            'siap' => [
                'Pesanan siap diambil!',
                "Pesanan {$kode} sudah siap. Tunjukkan kode pickup " . ($transaksi->kode_pickup ?? '-') . " ke kasir ya.",
                'success',
            ],
            'batal' => [
                'Pesanan dibatalkan',
                "Pesanan {$kode} dibatalkan oleh toko.",
                'error',
            ],
            'selesai' => [
                'Pesanan selesai',
                "Pesanan {$kode} sudah kamu ambil. Terima kasih sudah belanja di Saisho!",
                'success',
            ],
            default => null,
        };
    }

    private static function getIconHtml(string $iconType): string
    {
        switch ($iconType) {
            case 'warning':
                return '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
            case 'amber':
                return '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>';
            case 'error':
                return '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>';
            case 'success':
                return '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>';
            default:
                return '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
        }
    }

    private static function getColorClass(string $iconType): string
    {
        switch ($iconType) {
            case 'warning':
                return 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400';
            case 'amber':
                return 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400';
            case 'error':
                return 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400';
            case 'success':
                return 'bg-green-50 text-green-600 dark:bg-green-500/10 dark:text-green-400';
            default:
                return 'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400';
        }
    }
}