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
        try {
            Produksi::where('status', '!=', 'kadaluarsa')
                ->get()
                ->each(function ($produksi) {
                    if ($produksi->is_kadaluarsa) {
                        $produksi->status = 'kadaluarsa';
                        $produksi->save();
                    }
                });

            $this->info('Status produksi telah diperbarui');
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            activity('error')
                ->withProperties([
                    'error_message' => $e->getMessage(),
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'command' => $this->signature,
                ])
                ->log('Error pada command produksi:update-status: ' . $e->getMessage());

            $this->error('Gagal memperbarui status produksi: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}