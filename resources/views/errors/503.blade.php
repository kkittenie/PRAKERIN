@extends('layouts.fullscreen-layout')

@section('content')
<x-common.error-page
    code="503"
    image="maintenance"
    accent="brand"
    title="Sedang Pemeliharaan"
    message="Daisy POS sedang dalam pemeliharaan singkat untuk peningkatan layanan. Coba akses lagi beberapa saat lagi."
/>
@endsection