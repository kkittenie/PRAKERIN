<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Produksi;
use Carbon\Carbon;
use App\Events\StatusProduksiUpdate;

class UpdateStatusProduksi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'produksi:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();
        Produksi::all()->each(function ($produksi) use ($now) {
            if ($produksi->kadaluarsa <= $now) {
                $produksi->status = 'kadaluarsa';
                $produksi->save();
            }
        });
        $this->info('Status produksi telah diperbarui');
        return Command::SUCCESS;
    }
}
