{{--
    Daftarkan token push notification (FCM) device ini ke user yang sedang
    login. Cuma jalan di dalam aplikasi Android (WebView), karena token
    diambil lewat jembatan window.Android.getFcmToken(). Di browser biasa
    tidak ada jembatan itu, jadi script ini diam saja.
--}}
<script>
    (function () {
        if (!window.Android || typeof window.Android.getFcmToken !== 'function') return;

        const csrf = document.querySelector('meta[name="csrf-token"]');
        if (!csrf) return;

        // Penanda per-sesi login (di-hash). Login/logout bikin session id baru,
        // jadi setelah login ulang token otomatis didaftarkan lagi.
        const kunciCache = 'fcm_admin_token:{{ substr(sha1(session()->getId()), 0, 16) }}';

        const kirimToken = function (token) {
            // Sudah didaftarkan di sesi login ini -> nggak perlu kirim ulang.
            try {
                if (sessionStorage.getItem(kunciCache) === token) return;
            } catch (e) {}

            fetch("{{ route('notifications.token') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf.content,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ token: token, platform: 'android' }),
            })
                .then(function (res) {
                    if (res.ok) {
                        try { sessionStorage.setItem(kunciCache, token); } catch (e) {}
                    }
                })
                .catch(function () {});
        };

        // Token bisa kosong kalau Firebase belum selesai bikin token (biasanya
        // cuma di pembukaan pertama setelah install), jadi dicoba beberapa kali.
        let percobaan = 0;
        const ambilToken = function () {
            let token = '';
            try { token = window.Android.getFcmToken(); } catch (e) {}

            if (token) {
                kirimToken(token);
            } else if (++percobaan < 5) {
                setTimeout(ambilToken, 3000);
            }
        };

        ambilToken();
    })();
</script>