{{--
    Login Google native untuk aplikasi Android (WebView).
    Google menolak OAuth di dalam WebView, jadi di aplikasi tombol Google memanggil
    Android.loginGoogle() (popup akun Google bawaan). Hasilnya (ID token) dikirim ke server
    lewat form tersembunyi ini. Di browser biasa / APK lama, tombol tetap jalan sebagai link OAuth.
    Tombol yang ingin memakai ini cukup diberi atribut data-google-native.
--}}
<form id="form-google-native" method="POST" action="{{ route('member.google.token') }}" class="hidden">
    @csrf
    <input type="hidden" name="id_token" id="google-native-token">
</form>

<script>
    (function () {
        var tombolAktif = null;

        function nativeTersedia() {
            return !!(window.Android && typeof window.Android.loginGoogle === 'function');
        }

        function selesai() {
            if (tombolAktif) {
                tombolAktif.dataset.sibuk = '0';
                tombolAktif.style.opacity = '';
                tombolAktif = null;
            }
        }

        document.addEventListener('click', function (e) {
            var el = e.target.closest ? e.target.closest('[data-google-native]') : null;

            // Bukan di aplikasi Android (atau APK lama): biarkan link OAuth biasa berjalan
            if (!el || !nativeTersedia()) return;

            e.preventDefault();
            if (el.dataset.sibuk === '1') return;

            el.dataset.sibuk = '1';
            el.style.opacity = '0.6';
            tombolAktif = el;
            window.Android.loginGoogle();
        });

        // Dipanggil aplikasi Android setelah pengguna memilih akun Google
        window.onGoogleNativeToken = function (token) {
            document.getElementById('google-native-token').value = token;
            document.getElementById('form-google-native').submit();
        };

        // Dipanggil aplikasi Android kalau dibatalkan / gagal (pesan error ditampilkan sebagai Toast oleh aplikasi)
        window.onGoogleNativeError = function () {
            selesai();
        };
    })();
</script>