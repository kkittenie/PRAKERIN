<?php

namespace App\Listeners;

use App\Events\StatusProduksiUpdate;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateStatusProduksi
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(StatusProduksiUpdate $event): void
    {
        $produksi = $event->produksi;

        if (! $produksi->is_kadaluarsa) {
            $produksi->update(['status' => 'belum kadaluwarsa']);
        }
    }
}