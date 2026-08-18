<?php

namespace App\Console\Commands;

use App\Models\Promo;
use Illuminate\Console\Command;

class NonaktifkanPromoKadaluwarsa extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'promo:tandai-kadaluwarsa';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menonaktifkan promo yang sudah lewat masa berlakunya(kadaluwarsa)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $promoKadaluwarsa = Promo::where('status_aktif', true)
                ->whereNotNull('selesai_berlaku')
                ->where('selesai_berlaku', '<', now())
                ->get();

            foreach ($promoKadaluwarsa as $promo) {
                $promo->update(['status_aktif' => false]);

                activity()
                    ->performedOn($promo)
                    ->withProperties(['selesai_berlaku' => $promo->selesai_berlaku])
                    ->log('promo otomatis dinonaktifkan karena kadaluwarsa');
            }
            
            $jumlah = $promoKadaluwarsa->count();
            $this->info("Berhasil menonaktifkan {$jumlah} promo yang sudah kadaluwarsa.");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            activity()
                ->withProperties([
                    'error_message' => $e->getMessage(),
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'command' => $this->signature,
                ])
                ->log('Error pada command promo:tandai-kadaluwarsa: ' . $e->getMessage());

            $this->error('Gagal menonaktifkan promo kadaluwarsa: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
