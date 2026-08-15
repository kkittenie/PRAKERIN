<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('pesanan-pickup', function ($user) {
    return $user->can('lihat_pesanan_pickup');
});
