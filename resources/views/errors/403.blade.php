@extends('layouts.fullscreen-layout')

@section('content')
<x-common.error-page
    code="403"
    image="403"
    accent="red"
    title="Akses Ditolak"
    message="Kamu tidak punya izin untuk mengakses halaman ini. Hubungi admin jika ini seharusnya bisa kamu akses."
/>
@endsection