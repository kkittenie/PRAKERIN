{{-- Settings / Printer Settings Dropdown Component --}}
<div class="relative" x-data="{
    dropdownOpen: false,
    isScanning: false,
    isConnecting: false,
    isConnected: false,

    selectedPrinter: '',
    availablePrinters: [],

    toasts: [],
    toastCounter: 0,

    posisiAtas: 0,
    posisiKiri: 0,
    tinggiMax: 500,

    tambahToast(pesan, tipe = 'sukses') {
        const id = this.toastCounter++;
        this.toasts.push({ id, pesan, tipe });
        setTimeout(() => {
            this.toasts = this.toasts.filter(t => t.id !== id);
        }, 3000);
    },

    init() {
        this.refreshPrinters();
    },

    toggleDropdown() {
        this.dropdownOpen = !this.dropdownOpen;
        if (this.dropdownOpen) {
            this.$nextTick(() => this.hitungPosisi());
        }
    },

    closeDropdown() {
        this.dropdownOpen = false;
    },

    hitungPosisi() {
        const margin = 12;
        const tombol = this.$refs.tombolSetelan.getBoundingClientRect();
        const panel = this.$refs.panelSetelan;
        const lebarPanel = panel.offsetWidth;

        let kiri = tombol.right - lebarPanel;
        kiri = Math.max(margin, Math.min(kiri, window.innerWidth - lebarPanel - margin));

        let atas = tombol.bottom + margin;
        let tinggi = window.innerHeight - atas - margin;

        // Jika ruang di bawah tombol terlalu sempit (misal hp/tab landscape),
        // pastikan tetap ada tinggi minimum yang wajar untuk menampilkan isi panel.
        if (tinggi < 250) {
            tinggi = Math.max(220, window.innerHeight - margin * 2);
            atas = margin;
        }

        this.posisiKiri = kiri;
        this.posisiAtas = atas;
        this.tinggiMax = tinggi;
    },

    refreshPrinters() {
        if (!window.Android) return;

        try {
            let data = Android.getPairedPrinters();

            this.availablePrinters = JSON.parse(data);

            if (this.availablePrinters.length > 0) {
                this.selectedPrinter = this.availablePrinters[0].address;
            }

        } catch (e) {
            console.error(e);
        }
    },

    scanPrinter() {
        this.isScanning = true;
        this.refreshPrinters();
        setTimeout(() => {
            this.isScanning = false;
        }, 1500);
    },

    toggleConnect() {

        if (!this.selectedPrinter) {
            this.tambahToast('Pilih printer terlebih dahulu.', 'error');
            return;
        }

        this.isConnecting = true;

        setTimeout(() => {
            try {
                const result = Android.connectPrinter(
                    this.selectedPrinter
                );

                console.log('CONNECT RESULT:', result);

                this.isConnected = result === 'true' || result === true;

                if (this.isConnected) {
                    this.tambahToast('Berhasil menyambungkan printer.', 'sukses');
                } else {
                    this.tambahToast('Gagal menyambungkan printer.', 'error');
                }

            } catch(e) {

                console.error(e);

                this.isConnected = false;
                this.tambahToast('Gagal menyambungkan printer.', 'error');

            } finally {
                this.isConnecting = false;
            }
        }, 400);

    }
}" @click.away="closeDropdown()" @resize.window="if (dropdownOpen) hitungPosisi()" @scroll.window="if (dropdownOpen) hitungPosisi()">
    <!-- Settings Button -->
    <button
        x-ref="tombolSetelan"
        class="relative flex items-center justify-center text-gray-500 transition-all duration-200 bg-white border border-gray-200 rounded-full hover:text-gray-900 h-11 w-11 hover:bg-gray-100 hover:scale-105 active:scale-95 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white cursor-pointer"
        @click="toggleDropdown()" type="button" aria-label="Pengaturan Printer">
        <!-- Crisp Heroicons Gear / Settings Icon -->
        <svg class="w-5 h-5 transition-transform duration-300 hover:rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
    </button>

    <!-- Dropdown Panel -->
    <div
        x-ref="panelSetelan"
        x-show="dropdownOpen" x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        :style="`top:${posisiAtas}px; left:${posisiKiri}px; max-height:${tinggiMax}px;`"
        class="fixed w-[92vw] max-w-[320px] overflow-y-auto rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-lg dark:border-gray-800 dark:bg-gray-900 z-99999"
        style="display: none;">
        <!-- Header -->
        <div class="flex items-center justify-between pb-3 mb-3 border-b border-gray-100 dark:border-gray-800">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <h5 class="text-base font-bold text-gray-800 dark:text-white">Printer</h5>
            </div>
            <button @click="closeDropdown()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Toast Alert Inside Settings Panel -->
        <template x-for="toast in toasts" :key="toast.id">
            <div :class="{
                    'bg-green-600': toast.tipe === 'sukses',
                    'bg-red-600': toast.tipe === 'error',
                    'bg-amber-700': toast.tipe === 'info'
                 }"
                class="text-white px-3.5 py-2.5 rounded-xl shadow-sm flex items-center gap-2 mb-3 text-xs font-semibold transform transition-all duration-300 animate-slide-in">
                <span class="font-bold text-sm" x-text="toast.tipe === 'sukses' ? '✓' : '⚠'"></span>
                <span x-text="toast.pesan"></span>
            </div>
        </template>

        <div class="space-y-4">
            <!-- Status Section -->
            <div class="bg-gray-50 dark:bg-gray-800/50 p-3 rounded-xl border border-gray-100 dark:border-gray-800">
                <label
                    class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Status</label>
                <div class="flex items-center gap-2">
                    <span class="h-2.5 w-2.5 rounded-full transition-colors duration-300"
                        :class="isConnected ? 'bg-green-500 animate-pulse' : 'bg-red-500'"></span>
                    <span class="text-sm font-bold"
                        :class="isConnected ? 'text-green-600 dark:text-green-400' : 'text-red-500 dark:text-red-400'"
                        x-text="isConnected ? 'Terhubung' : 'Tidak Terhubung'"></span>
                </div>
            </div>

            <!-- Printer Selection -->
            <div>
                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">Printer</label>
                <div class="relative">
                    <select x-model="selectedPrinter"
                        class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent font-medium transition">
                        <template x-for="(printer, index) in availablePrinters" :key="printer.address || index">
                            <option :value="printer.address" x-text="printer.name"
                                :selected="selectedPrinter === printer.address"></option>
                        </template>
                    </select>
                </div>
            </div>

            <!-- Scanning Feedback (If scanning) -->
            <div x-show="isScanning"
                class="p-2.5 bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 rounded-xl flex items-center justify-center gap-2 text-xs font-medium text-amber-700 dark:text-amber-400"
                x-transition>
                <svg class="animate-spin h-4 w-4 text-amber-600 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                <span>Mencari printer terdekat...</span>
            </div>

            <!-- Action Buttons -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-1">
                <button @click="scanPrinter()" :disabled="isScanning || isConnecting"
                    class="w-full px-3 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold rounded-xl text-xs transition-all duration-200 hover:scale-[1.02] active:scale-95 shadow-xs flex items-center justify-center gap-1.5 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    Scan Printer
                </button>

                <button @click="toggleConnect()" :disabled="isConnecting || isScanning"
                    class="w-full px-3 py-2 font-bold rounded-xl text-xs transition-all duration-200 shadow-sm hover:shadow-md hover:scale-[1.02] active:scale-95 flex items-center justify-center gap-1.5 cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed disabled:transform-none"
                    :class="isConnected
                        ? 'bg-red-600 hover:bg-red-700 active:bg-red-800 text-white'
                        : 'bg-amber-600 hover:bg-amber-700 active:bg-amber-800 text-white'">
                    
                    <!-- Loading Spinner Icon -->
                    <template x-if="isConnecting">
                        <svg class="animate-spin h-4 w-4 text-white shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </template>

                    <!-- Connect Icon (When not connecting) -->
                    <template x-if="!isConnecting">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </template>

                    <span x-text="isConnecting ? 'Menyambungkan...' : (isConnected ? 'Putuskan' : 'Sambungkan')"></span>
                </button>
            </div>
        </div>
    </div>
</div>