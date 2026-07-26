{{-- Settings / Printer Settings Dropdown Component --}}
<div class="relative" x-data="{
    dropdownOpen: false,
    isScanning: false,
    isConnected: false,

    selectedPrinter: '',
    availablePrinters: [],

    init() {
        console.log('INIT');

        if(window.Android){

            let data = Android.getPairedPrinters();

            console.log(data);

            this.availablePrinters = JSON.parse(data);

            if(this.availablePrinters.length){
                this.selectedPrinter = this.availablePrinters[0].address;
            }

        }
    },

    toggleDropdown() {
        this.dropdownOpen = !this.dropdownOpen;
    },

    closeDropdown() {
        this.dropdownOpen = false;
    },

    scanPrinter() {
        this.isScanning = true;
        this.refreshPrinters();
        setTimeout(() => {
            this.isScanning = false;
        }, 1500);
    },

    toggleConnect() {
        this.isConnected = !this.isConnected;
        if (window.Android && typeof Android.connectPrinter === 'function') {
            try {
                Android.connectPrinter(this.selectedPrinter);
            } catch (e) {
                console.error('Error calling Android.connectPrinter:', e);
            }
        }
    }
}" @click.away="closeDropdown()">
    <!-- Settings Button -->
    <button
        class="relative flex items-center justify-center text-gray-500 transition-colors bg-white border border-gray-200 rounded-full hover:text-gray-900 h-11 w-11 hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white"
        @click="toggleDropdown()"
        type="button"
        aria-label="Pengaturan Printer"
    >
        <!-- Crisp Heroicons Gear / Settings Icon -->
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
    </button>

    <!-- Dropdown Panel -->
    <div
        x-show="dropdownOpen"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="fixed inset-x-4 top-20 sm:absolute sm:inset-auto sm:right-0 sm:top-full sm:mt-3 w-auto sm:w-80 max-w-full sm:max-w-none rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-lg dark:border-gray-800 dark:bg-gray-900 z-99999"
        style="display: none;"
    >
        <!-- Header -->
        <div class="flex items-center justify-between pb-3 mb-3 border-b border-gray-100 dark:border-gray-800">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <h5 class="text-base font-bold text-gray-800 dark:text-white">Printer</h5>
            </div>
            <button @click="closeDropdown()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="space-y-4">
            <!-- Status Section -->
            <div class="bg-gray-50 dark:bg-gray-800/50 p-3 rounded-xl border border-gray-100 dark:border-gray-800">
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Status</label>
                <div class="flex items-center gap-2">
                    <span
                        class="h-2.5 w-2.5 rounded-full transition-colors duration-300"
                        :class="isConnected ? 'bg-green-500 animate-pulse' : 'bg-red-500'"
                    ></span>
                    <span
                        class="text-sm font-bold"
                        :class="isConnected ? 'text-green-600 dark:text-green-400' : 'text-red-500 dark:text-red-400'"
                        x-text="isConnected ? 'Connected' : 'Not Connected'"
                    ></span>
                </div>
            </div>

            <!-- Printer Selection -->
            <div>
                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">Printer</label>
                <div class="relative">
                    <select
                        x-model="selectedPrinter"
                        class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent font-medium"
                    >
                        <template x-for="(printer, index) in availablePrinters" :key="printer.address || index">
                            <option
                                :value="printer.address"
                                x-text="printer.name"
                                :selected="selectedPrinter === printer.address"
                            ></option>
                        </template>
                    </select>
                </div>
            </div>

            <!-- Scanning Feedback (If scanning) -->
            <div x-show="isScanning" class="p-2.5 bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 rounded-xl flex items-center justify-center gap-2 text-xs font-medium text-amber-700 dark:text-amber-400" x-transition>
                <svg class="animate-spin h-4 w-4 text-amber-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Mencari printer terdekat...</span>
            </div>

            <!-- Action Buttons -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-1">
                <button
                    @click="scanPrinter()"
                    :disabled="isScanning"
                    class="w-full px-3 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold rounded-xl text-xs transition flex items-center justify-center gap-1.5"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    Scan Printer
                </button>

                <button
                    @click="toggleConnect()"
                    class="w-full px-3 py-2 font-bold rounded-xl text-xs transition shadow-sm flex items-center justify-center gap-1.5"
                    :class="isConnected
                        ? 'bg-red-600 hover:bg-red-700 text-white'
                        : 'bg-amber-600 hover:bg-amber-700 text-white'"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <span x-text="isConnected ? 'Disconnect' : 'Connect'"></span>
                </button>
            </div>
        </div>
    </div>
</div>
