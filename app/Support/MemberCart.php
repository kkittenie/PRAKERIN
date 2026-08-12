<?php

namespace App\Support;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class MemberCart
{
    private const SESSION_KEY = 'member_cart';

    public static function items(): array
    {
        return Session::get(self::SESSION_KEY, []);
    }

    public static function isEmpty(): bool
    {
        return empty(self::items());
    }

    public static function jumlahItem(): int
    {
        return collect(self::items())->sum('jumlah');
    }

    public static function subtotal(): float
    {
        return collect(self::items())->sum(fn ($item) => $item['harga_satuan'] * $item['jumlah']);
    }

    public static function tambah(array $data): void
    {
        $data['produk_id'] = $data['produk_id'] ?? null;
        $data['bundling_id'] = $data['bundling_id'] ?? null;

        $items = self::items();

        $existingKey = null;
        foreach ($items as $key => $item) {
            if (
                ($item['produk_id'] ?? null) === $data['produk_id']
                && ($item['bundling_id'] ?? null) === $data['bundling_id']
                && ($item['ukuran'] ?? null) === ($data['ukuran'] ?? null)
                && ($item['catatan'] ?? null) === ($data['catatan'] ?? null)
            ) {
                $existingKey = $key;
                break;
            }
        }

        if ($existingKey !== null) {
            $items[$existingKey]['jumlah'] += $data['jumlah'];
        } else {
            $data['id'] = (string) Str::uuid();
            $items[] = $data;
        }

        Session::put(self::SESSION_KEY, array_values($items));
    }

    public static function ubahJumlah(string $itemId, int $jumlah): void
    {
        $items = self::items();

        if ($jumlah <= 0) {
            $items = array_filter($items, fn ($item) => $item['id'] !== $itemId);
        } else {
            foreach ($items as &$item) {
                if ($item['id'] === $itemId) {
                    $item['jumlah'] = $jumlah;
                }
            }
            unset($item);
        }

        Session::put(self::SESSION_KEY, array_values($items));
    }

    public static function hapus(string $itemId): void
    {
        $items = array_values(array_filter(
            self::items(),
            fn ($item) => $item['id'] !== $itemId
        ));

        Session::put(self::SESSION_KEY, $items);
    }

    public static function kosongkan(): void
    {
        Session::forget(self::SESSION_KEY);
    }
}
