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

        const sisipkanTokenKeFormLogout = function (token) {
            if (!token) return;
            document.querySelectorAll('form[action*="logout"]').forEach(function (form) {
                let input = form.querySelector('input[name="fcm_token"]');
                if (!input) {
                    input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'fcm_token';
                    form.appendChild(input);
                }
                input.value = token;
            });
        };

        const simpanTokenLokal = function (token) {
            if (!token) return;
            try {
                localStorage.setItem('fcm_admin_device_token', token);
                document.cookie = "fcm_token=" + encodeURIComponent(token) + "; path=/; max-age=31536000; SameSite=Lax";
            } catch (e) {}
            sisipkanTokenKeFormLogout(token);
        };

        const kirimToken = function (token) {
            simpanTokenLokal(token);

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

        // Tangani proses logout agar token di device dilepas dari akun admin
        const tanganiLogout = function (form) {
            let token = '';
            try {
                if (window.Android && typeof window.Android.getFcmToken === 'function') {
                    token = window.Android.getFcmToken();
                }
            } catch (err) {}
            if (!token) {
                try { token = localStorage.getItem('fcm_admin_device_token') || ''; } catch (err) {}
            }

            if (token) {
                if (form) {
                    let input = form.querySelector('input[name="fcm_token"]');
                    if (!input) {
                        input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'fcm_token';
                        form.appendChild(input);
                    }
                    input.value = token;
                }

                // Kirim request hapus token ke server dengan keepalive agar tetap selesai meski halaman berganti
                try {
                    fetch("{{ route('notifications.hapus-token') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf.content,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({ token: token }),
                        keepalive: true
                    }).catch(function () {});
                } catch (e) {}
            }

            // Bersihkan storage lokal
            try {
                sessionStorage.removeItem(kunciCache);
                localStorage.removeItem('fcm_admin_device_token');
                document.cookie = "fcm_token=; path=/; expires=Thu, 01 Jan 1970 00:00:00 UTC;";
            } catch (err) {}
        };

        // Event listener saat submit form logout
        document.addEventListener('submit', function (e) {
            const form = e.target;
            if (form && form.action && form.action.indexOf('logout') !== -1) {
                tanganiLogout(form);
            }
        }, true);

        // Event listener saat tombol/link logout diklik
        document.addEventListener('click', function (e) {
            const btn = e.target.closest('button, a');
            if (btn) {
                const form = btn.closest('form');
                if (form && form.action && form.action.indexOf('logout') !== -1) {
                    tanganiLogout(form);
                }
            }
        }, true);
    })();
</script>