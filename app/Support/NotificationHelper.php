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
use App\Models\User;
use App\Models\UserDeviceToken;
use Illuminate\Support\Facades\Log;

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

    /**
     * Bikin notifikasi dashboard admin (tampil di dropdown lonceng) + kirim
     * push notification (FCM) ke HP admin/kasir yang punya akses notifikasi.
     */
    public static function create(string $title, string $desc, string $type = 'sistem', ?string $url = null, string $iconType = 'info'): AppNotification
    {
        $notif = AppNotification::create([
            'title' => $title,
            'desc' => $desc,
            'type' => $type,
            'url' => $url,
            'icon_type' => $iconType,
            'is_read' => false,
        ]);

        static::pushKeAdmin($title, $desc, $url, $type, 'lihat_notifikasi');

        return $notif;
    }

    /**
     * Kirim push notification (FCM) ke semua device admin/kasir yang punya
     * permission tertentu. Cuma push, TIDAK bikin baris di app_notifications
     * (jadi aman dipakai buat event yang sudah punya notif di dropdown,
     * mis. pesanan pickup baru, tanpa jadi dobel).
     *
     * Nggak pernah melempar error: gagal kirim push cuma dicatat ke log
     * supaya proses utama (checkout, dsb) tidak ikut gagal.
     */
    public static function pushKeAdmin(string $title, string $body, ?string $url = null, string $type = 'sistem', string $permission = 'lihat_notifikasi'): void
    {
        try {
            $userIds = User::permission($permission)->get()->pluck('id')->all();

            if (empty($userIds)) {
                return;
            }

            $tokens = UserDeviceToken::whereIn('user_id', $userIds)->pluck('token')->all();

            if (empty($tokens)) {
                return;
            }

            $tokensGagal = (new FcmService())->kirimKeTokens($tokens, $title, $body, [
                'url' => $url ?? '',
                'type' => $type,
            ]);

            if (! empty($tokensGagal)) {
                UserDeviceToken::whereIn('token', $tokensGagal)->delete();
            }
        } catch (\Throwable $e) {
            Log::error('Gagal kirim push notifikasi ke admin: ' . $e->getMessage());
        }
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
                'Order Received',
                "Your order {$kode} has been received. Please complete your payment.",
                'info',
            ],
            'lunas' => [
                'Payment Confirmed',
                "Payment for order {$kode} has been verified. We are preparing your order shortly.",
                'success',
            ],
            'tolak_bukti' => [
                'Payment Receipt Rejected',
                ($transaksi->catatan_penolakan ?: 'Payment receipt is invalid') . ". Please re-upload your payment receipt for {$kode}.",
                'error',
            ],
            'diproses' => [
                'Order in Preparation',
                "Your order {$kode} is currently being baked and prepared by our kitchen.",
                'info',
            ],
            'siap' => [
                'Order Ready for Pickup!',
                "Your order {$kode} is ready. Present pickup code " . ($transaksi->kode_pickup ?? '-') . " to the cashier.",
                'success',
            ],
            'batal' => [
                'Order Cancelled',
                "Order {$kode} was cancelled by the store. Reason: " . ($transaksi->alasan_pembatalan ?: '-') . ". Contact customer service for refund assistance.",
                'error',
            ],
            'batal_otomatis' => [
                'Order Automatically Cancelled',
                "Order {$kode} was cancelled automatically due to payment expiration within " . Transaksi::BATAS_BAYAR_MENIT . " minutes. Reserved items & vouchers have been restored.",
                'error',
            ],
            'selesai' => [
                'Order Completed',
                "Order {$kode} has been collected. Thank you for baking with Daisy Bakery!",
                'success',
            ],
            default => null,
        };
    }

    public static function translateTitle(?string $title): string
    {
        if (! $title) return '';
        return match (trim($title)) {
            'Pesanan diterima' => 'Order Received',
            'Pembayaran dikonfirmasi' => 'Payment Confirmed',
            'Bukti pembayaran ditolak' => 'Payment Receipt Rejected',
            'Pesanan sedang diproses' => 'Order in Preparation',
            'Pesanan siap diambil!', 'Pesanan siap diambil' => 'Order Ready for Pickup!',
            'Pesanan dibatalkan' => 'Order Cancelled',
            'Pesanan dibatalkan otomatis' => 'Order Automatically Cancelled',
            'Pesanan selesai' => 'Order Completed',
            'Stamp baru diperoleh' => 'New Stamp Earned',
            'Hadiah stamp berhasil ditukar' => 'Stamp Reward Redeemed',
            'Hadiah Stamp Masuk ke Keranjang' => 'Stamp Reward Added to Cart',
            'Penukaran Stamp Berhasil' => 'Stamp Redemption Successful',
            'Pesanan Dibatalkan - Hadiah Stamp Siap Diambil' => 'Order Cancelled - Stamp Reward Restored',
            default => $title,
        };
    }

    public static function translateDesc(?string $desc): string
    {
        if (! $desc) return '';
        $desc = trim($desc);

        if (preg_match('/Pesanan\s+([A-Z0-9\-]+)\s+kamu sudah kami terima,\s*selesaikan pembayaran ya\./i', $desc, $m)) {
            return "Your order {$m[1]} has been received. Please complete your payment.";
        }

        if (preg_match('/Pesanan\s+([A-Z0-9\-]+)\s+dibatalkan otomatis karena belum ada konfirmasi pembayaran dalam\s+(\d+)\s+menit\..*/i', $desc, $m)) {
            return "Order {$m[1]} was automatically cancelled due to payment expiration within {$m[2]} minutes. Reserved items & vouchers have been restored.";
        }

        if (preg_match('/Pembayaran pesanan\s+([A-Z0-9\-]+)\s+sudah dikonfirmasi toko\..*/i', $desc, $m)) {
            return "Payment for order {$m[1]} has been confirmed. Your artisanal bakes are being prepared shortly.";
        }

        if (preg_match('/Pesanan\s+([A-Z0-9\-]+)\s+kamu sedang disiapkan oleh toko\./i', $desc, $m)) {
            return "Your order {$m[1]} is currently being baked and prepared fresh by our kitchen.";
        }

        if (preg_match('/Pesanan\s+([A-Z0-9\-]+)\s+sudah siap\.\s*Tunjukkan kode pickup\s+([^\s]+)\s+ke kasir ya\./i', $desc, $m)) {
            return "Your order {$m[1]} is ready for collection! Present pickup code {$m[2]} to the cashier.";
        }

        if (preg_match('/Pesanan\s+([A-Z0-9\-]+)\s+dibatalkan oleh toko\.\s*Alasan:\s*([^.]+)\..*/i', $desc, $m)) {
            return "Order {$m[1]} was cancelled by the bakery. Reason: {$m[2]}. Please contact our concierge for refund.";
        }

        if (preg_match('/Pesanan\s+([A-Z0-9\-]+)\s+sudah kamu ambil\..*/i', $desc, $m)) {
            return "Order {$m[1]} has been collected. Thank you for baking with Daisy Bakery!";
        }

        if (preg_match('/(.+)\.\s*Silakan upload ulang bukti pembayaran\s+([A-Z0-9\-]+)\./i', $desc, $m)) {
            return "{$m[1]}. Please re-upload your payment receipt for {$m[2]}.";
        }

        if (preg_match('/Hadiah\s+(.+)\s+siap dipesan via aplikasi\.\s*Silakan selesaikan checkout\./i', $desc, $m)) {
            return "Reward {$m[1]} is ready to order via app. Please proceed to checkout.";
        }

        if (preg_match('/Kamu berhasil menukar stamp dengan\s+(.+)\.\s*Kode:\s*([^\s]+)/i', $desc, $m)) {
            return "You successfully redeemed stamps for {$m[1]}. Redemption Code: {$m[2]}";
        }

        if (preg_match('/Pesanan\s+#?([A-Z0-9\-]+)\s+(.+)\.\s*Hadiah\s+(.+)\s*\(Kode:\s*([^)]+)\)\s+siap diambil kembali via aplikasi atau kasir\./i', $desc, $m)) {
            return "Order #{$m[1]} was cancelled. Reward {$m[3]} (Code: {$m[4]}) has been restored and is ready to claim.";
        }

        return $desc;
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