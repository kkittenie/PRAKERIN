@extends('layouts.fullscreen-layout')

@section('content')
<x-common.error-page
    code="404"
    image="404"
    accent="brand"
    title="Halaman Tidak Ditemukan"
    message="Halaman yang kamu cari mungkin sudah dipindahkan, dihapus, atau alamatnya salah ketik."
/>
@endsection