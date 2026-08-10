<?php

namespace App\Console\Commands;

use App\Models\PesananDitahan;
use App\Models\Transaksi;
use Illuminate\Console\Command;

class BersihkanDiskonLabelRusak extends Command
{
    protected $signature = 'diskon:bersihkan-label {--dry-run : Tampilkan saja perubahan tanpa menyimpan}';

    protected $description = 'Bersihkan teks diskon_label yang rusak akibat bug lama (persentase > 100%) di transaksi & pesanan ditahan';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $totalDiperbaiki = 0;

        foreach ([Transaksi::class, PesananDitahan::class] as $modelClass) {
            $query = $modelClass::whereNotNull('diskon_label')->where('diskon_label', '!=', '');

            $query->each(function ($record) use (&$totalDiperbaiki, $dryRun, $modelClass) {
                $decoded = json_decode($record->diskon_label, true);
                if (!is_array($decoded)) {
                    return;
                }

                $berubah = false;
                foreach ($decoded as &$item) {
                    if (!isset($item['nama']) || !is_string($item['nama'])) {
                        continue;
                    }

                    if (preg_match('/\s*\((\d+(?:,\d+)?)%\)\s*$/', $item['nama'], $m)) {
                        $persen = (float) str_replace(',', '.', $m[1]);
                        if ($persen > 100) {
                            $item['nama'] = trim(preg_replace('/\s*\(\d+(?:,\d+)?%\)\s*$/', '', $item['nama']));
                            $berubah = true;
                        }
                    }
                }
                unset($item);

                if ($berubah) {
                    $totalDiperbaiki++;
                    $labelBaru = json_encode($decoded);
                    $this->line(sprintf(
                        '[%s#%d] %s -> %s',
                        class_basename($modelClass),
                        $record->id,
                        $record->diskon_label,
                        $labelBaru
                    ));

                    if (!$dryRun) {
                        $record->diskon_label = $labelBaru;
                        $record->save();
                    }
                }
            });
        }

        if ($totalDiperbaiki === 0) {
            $this->info('Tidak ada diskon_label yang rusak ditemukan.');
        } else {
            $this->info(($dryRun ? '[DRY RUN] ' : '') . "Selesai. {$totalDiperbaiki} baris diperbaiki.");
        }

        return Command::SUCCESS;
    }
}