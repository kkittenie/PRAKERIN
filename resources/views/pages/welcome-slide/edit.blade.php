@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Edit Slide Welcome" parentTitle="Slide Welcome" :parentUrl="route('welcome-slide.index')" />

<div class="space-y-6">
    <x-common.component-card title="Form Edit Slide Welcome">
        <form action="{{ route('welcome-slide.update', $slide->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('pages.welcome-slide.partials.form', ['slide' => $slide])

            <div class="mt-8 flex justify-end gap-3">
                <a href="{{ route('welcome-slide.index') }}" class="rounded-lg border border-gray-300 dark:border-gray-700 px-5 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">Batal</a>
                <button class="rounded-lg bg-brand-500 px-5 py-2 text-white hover:bg-brand-600">Simpan Perubahan</button>
            </div>
        </form>
    </x-common.component-card>
</div>

@endsection