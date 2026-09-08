@extends('layouts.fullscreen-layout')

@section('content')
<x-common.error-page
    code="419"
    accent="amber"
    title="Sesi Kamu Sudah Kadaluarsa"
    message="Demi keamanan, sesi otomatis berakhir setelah tidak aktif beberapa saat. Silakan kembali dan coba lagi."
/>
@endsection