@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Edit Level Membership" parentTitle="Aturan Naik Level" :parentUrl="route('member-level.index')" />

<div class="space-y-6">

    <x-common.component-card title="Form Edit Level Membership">

        <form action="{{ route('member-level.update', $level->id) }}" method="POST" x-data="{
                warnaBadge: '{{ old('warna_badge', $level->warna_badge) }}',
                badgeClasses: {
                    gray: 'bg-gray-100 text-gray-600 dark:bg-gray-500/10 dark:text-gray-300',
                    amber: 'bg-amber-50 text-amber-800 dark:bg-amber-500/10 dark:text-amber-400',
                    slate: 'bg-slate-100 text-slate-600 dark:bg-slate-500/10 dark:text-slate-300',
                    yellow: 'bg-yellow-50 text-yellow-700 dark:bg-yellow-500/10 dark:text-yellow-400',
                    blue: 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400',
                    green: 'bg-green-50 text-green-700 dark:bg-green-500/10 dark:text-green-400',
                    purple: 'bg-purple-50 text-purple-700 dark:bg-purple-500/10 dark:text-purple-400',
                    red: 'bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400',
                },
                minimalTransaksiDisplay: '{{ old('minimal_transaksi', $level->minimal_transaksi) !== null ? number_format((float) old('minimal_transaksi', $level->minimal_transaksi), 0, ',', '.') : '0' }}',
                getRawNumber(val) {
                    return (val || '').toString().replace(/\D/g, '') || '0';
                },
                handleRupiahInput(event, prop) {
                    const input = event.target;
                    const oldValue = input.value;
                    const cursorPos = input.selectionStart;
                    const digitsBeforeCursor = oldValue.slice(0, cursorPos).replace(/\D/g, '').length;

                    const rawDigits = oldValue.replace(/\D/g, '');
                    const formatted = rawDigits ? new Intl.NumberFormat('id-ID').format(rawDigits) : '';

                    this[prop] = formatted;

                    this.$nextTick(() => {
                        let newPos = formatted.length;
                        let count = 0;
                        for (let i = 0; i < formatted.length; i++) {
                            if (/\d/.test(formatted[i])) {
                                count++;
                                if (count === digitsBeforeCursor) {
                                    newPos = i + 1;
                                    break;
                                }
                            }
                        }
                        if (digitsBeforeCursor === 0) newPos = 0;
                        input.setSelectionRange(newPos, newPos);
                    });
                }
            }">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Nama Level <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nama_level" value="{{ old('nama_level', $level->nama_level) }}"
                        placeholder="Contoh: Bronze, Silver, Gold"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500 placeholder-gray-400"
                        required>
                    @error('nama_level')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Minimal Transaksi (Rp) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 top-3 text-gray-500 font-bold text-sm">Rp</span>
                        <input type="text" inputmode="numeric" autocomplete="off" x-model="minimalTransaksiDisplay"
                            @input="handleRupiahInput($event, 'minimalTransaksiDisplay')"
                            placeholder="Contoh: 50.000"
                            class="h-12 w-full rounded-lg border border-gray-300 bg-white pl-12 pr-4 py-3 text-gray-900 font-semibold dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:border-brand-500 focus:ring-brand-500"
                            required>
                        <input type="hidden" name="minimal_transaksi" :value="getRawNumber(minimalTransaksiDisplay)">
                    </div>
                    <span class="text-xs text-gray-400 dark:text-gray-500 mt-1 block">Total nominal transaksi minimal yang harus dicapai member untuk naik ke level ini.</span>
                    @error('minimal_transaksi')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Warna Badge
                    </label>
                    <div class="flex flex-wrap items-center gap-3">
                        <select name="warna_badge" x-model="warnaBadge"
                            class="h-12 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">
                            <option value="gray">Abu-abu</option>
                            <option value="amber">Amber (Bronze)</option>
                            <option value="slate">Slate (Silver)</option>
                            <option value="yellow">Kuning (Gold)</option>
                            <option value="blue">Biru</option>
                            <option value="green">Hijau</option>
                            <option value="purple">Ungu</option>
                            <option value="red">Merah</option>
                        </select>

                        <span class="text-xs text-gray-400">Preview:</span>
                        <span class="rounded-full px-3 py-1 text-xs font-medium"
                            :class="badgeClasses[warnaBadge] ?? badgeClasses.gray">{{ $level->nama_level }}</span>
                    </div>
                    @error('warna_badge')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Status Level
                    </label>
                    <label class="flex items-center gap-3 h-12 cursor-pointer">
                        <input type="hidden" name="status_aktif" value="0">
                        <input type="checkbox" name="status_aktif" value="1"
                            class="w-5 h-5 rounded border-gray-300 text-brand-500 focus:ring-brand-500"
                            {{ old('status_aktif', $level->status_aktif) ? 'checked' : '' }}>
                        <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">Aktifkan Level Ini</span>
                    </label>
                    @error('status_aktif')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('member-level.index') }}"
                   class="rounded-lg border border-gray-300 dark:border-gray-700 px-5 py-2 hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300">
                    Batal
                </a>
                <button type="submit"
                    class="rounded-lg bg-brand-500 px-5 py-2 text-white hover:bg-brand-600">
                    Simpan Perubahan
                </button>
            </div>

        </form>

    </x-common.component-card>

</div>

@endsection