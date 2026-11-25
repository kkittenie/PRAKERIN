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
    },

    closeDropdown() {
        this.dropdownOpen = false;
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
                const result = Android.connectPrinter(this.selectedPrinter);
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
}" @click.away="closeDropdown()">
    <button
        x-ref="tombolSetelan"
        class="relative flex items-center justify-center text-[#4A2E27] transition-all duration-200 bg-[#FFFDF9] border border-[#3C2415]/15 rounded-full hover:text-[#8E1E26] h-10 w-10 sm:h-11 sm:w-11 hover:bg-[#FCEEEF] hover:border-[#8E1E26]/30 dark:bg-[#241A16] dark:border-white/10 dark:text-[#EACE80] dark:hover:bg-[#2E211C] cursor-pointer"
        @click="toggleDropdown()" type="button" aria-label="Pengaturan Printer">
        <svg class="w-5 h-5 transition-transform duration-300 hover:rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
    </button>

    <div
        x-ref="panelSetelan"
        x-show="dropdownOpen" x-cloak x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="transform opacity-0 scale-95 -translate-y-1" x-transition:enter-end="transform opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100" x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="fixed left-3 right-3 top-16 sm:absolute sm:top-full sm:left-auto sm:right-0 sm:mt-3 sm:w-[330px] overflow-y-auto rounded-2xl border border-[#3C2415]/15 bg-[#FFFDF9] p-4 shadow-[0_18px_36px_-10px_rgba(58,34,27,0.22)] dark:border-white/10 dark:bg-[#241A16] z-50 max-h-[82vh]"
        style="display: none;">
        <div class="flex items-center justify-between pb-3 mb-3 border-b border-[#3C2415]/10 dark:border-white/10">
            <div class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-xl bg-[#FCEEEF] dark:bg-[#8E1E26]/25 flex items-center justify-center text-[#8E1E26] dark:text-[#EACE80]">
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                </span>
                <h5 class="font-display text-base font-bold text-[#3A221B] dark:text-[#FAF5E8]">Pengaturan Printer</h5>
            </div>
            <button @click="closeDropdown()" class="text-[#8A766B] hover:text-[#3A221B] dark:hover:text-[#FAF5E8] transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <template x-for="toast in toasts" :key="toast.id">
            <div :class="{
                    'bg-[#2C5835]': toast.tipe === 'sukses',
                    'bg-[#8E1E26]': toast.tipe === 'error',
                    'bg-[#3A221B]': toast.tipe === 'info'
                 }"
                class="text-[#FAF5E8] px-3.5 py-2.5 rounded-xl shadow-sm flex items-center gap-2 mb-3 text-xs font-semibold">
                <span class="font-bold text-sm" x-text="toast.tipe === 'sukses' ? '✓' : '!'"></span>
                <span x-text="toast.pesan"></span>
            </div>
        </template>

        <div class="space-y-4">
            <div class="bg-[#FAF5E8] dark:bg-[#2E211C] p-3 rounded-xl border border-[#3C2415]/10 dark:border-white/10">
                <label class="block text-[11px] font-bold text-[#7D6A60] dark:text-[#C5B5A8] uppercase tracking-wider mb-1">Status Koneksi</label>
                <div class="flex items-center gap-2">
                    <span class="h-2.5 w-2.5 rounded-full transition-colors duration-300"
                        :class="isConnected ? 'bg-[#2C5835] animate-pulse' : 'bg-[#8E1E26]'"></span>
                    <span class="text-sm font-bold"
                        :class="isConnected ? 'text-[#2C5835] dark:text-green-400' : 'text-[#8E1E26] dark:text-[#F09A9E]'"
                        x-text="isConnected ? 'Terhubung' : 'Tidak Terhubung'"></span>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-[#6B574C] dark:text-[#C5B5A8] mb-1.5">Perangkat Printer</label>
                <div class="relative">
                    <select x-model="selectedPrinter"
                        class="w-full px-3 py-2 text-sm border border-[#3C2415]/18 dark:border-white/15 bg-[#FFFDF9] dark:bg-[#2E211C] text-[#3A221B] dark:text-[#FAF5E8] rounded-xl font-medium transition">
                        <option value="" x-show="availablePrinters.length === 0">Tidak ada printer terdeteksi</option>
                        <template x-for="(printer, index) in availablePrinters" :key="printer.address || index">
                            <option :value="printer.address" x-text="printer.name"
                                :selected="selectedPrinter === printer.address"></option>
                        </template>
                    </select>
                </div>
            </div>

            <div x-show="isScanning"
                class="p-2.5 bg-[#FCEEEF] dark:bg-[#8E1E26]/20 border border-[#8E1E26]/20 rounded-xl flex items-center justify-center gap-2 text-xs font-semibold text-[#8E1E26] dark:text-[#EACE80]"
                x-transition>
                <svg class="animate-spin h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Mencari printer terdekat...</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-1">
                <button @click="scanPrinter()" :disabled="isScanning || isConnecting"
                    class="w-full px-3 py-2.5 bg-[#F4ECE0] hover:bg-[#EAE0D0] dark:bg-[#2E211C] dark:hover:bg-[#3A2A23] text-[#3A221B] dark:text-[#FAF5E8] font-bold rounded-xl text-xs transition flex items-center justify-center gap-1.5 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    Scan Printer
                </button>

                <button @click="toggleConnect()" :disabled="isConnecting || isScanning"
                    class="w-full px-3 py-2.5 font-bold rounded-xl text-xs transition shadow-xs flex items-center justify-center gap-1.5 cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed"
                    :class="isConnected
                        ? 'bg-[#3A221B] hover:bg-[#231815] text-[#FAF5E8]'
                        : 'bg-[#8E1E26] hover:bg-[#741A21] text-[#FAF5E8]'">
                    <template x-if="isConnecting">
                        <svg class="animate-spin h-4 w-4 text-white shrink-0" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </template>
                    <template x-if="!isConnecting">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </template>
                    <span x-text="isConnecting ? 'Menyambungkan...' : (isConnected ? 'Putuskan' : 'Sambungkan')"></span>
                </button>
            </div>
        </div>
    </div>
</div>
