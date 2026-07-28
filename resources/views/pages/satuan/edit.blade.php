@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Edit Satuan" />

<div class="space-y-6">

    <x-common.component-card title="Form Edit Satuan">

        <form action="{{ route('satuan.update', $satuan->id) }}" method="POST">

            @csrf
            @method('PUT')

            <div class="mb-6">

                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Nama Satuan
                    <span class="text-red-500">*</span>
                </label>

                <input
                    type="text"
                    name="nama"
                    value="{{ old('nama', $satuan->nama) }}"
                    placeholder="Contoh : Kg"
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500 placeholder-gray-400">

                @error('nama')
                    <p class="mt-2 text-sm text-red-500">
                        {{ $message }}
                    </p>
                @enderror

            </div>

            <div class="flex justify-end gap-3">

                <a
                    href="{{ route('satuan.index') }}"
                    class="rounded-lg border border-gray-300 dark:border-gray-700 px-5 py-2 hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300">

                    Batal

                </a>

                <button
                    type="submit"
                    class="rounded-lg bg-brand-500 px-5 py-2 text-white hover:bg-brand-600">

                    Update

                </button>

            </div>

        </form>

    </x-common.component-card>

</div>

@endsection