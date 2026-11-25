@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Tambah Bundling" parentTitle="Bundling" :parentUrl="route('bundling.index')" />

<div class="space-y-6">

<x-common.component-card title="Informasi Bundling">
    @include('admin.bundling.partials.form', ['bundling' => null, 'isiTersimpan' => []])
</x-common.component-card>

</div>

@endsection