<?php

namespace App\Helpers;

class MenuHelper
{
    public static function getMainNavItems()
    {
        return [
            [
                'name' => 'Dashboard',
                'icon' => 'dashboard',
                'subItems' => [
                    ['name' => 'Penjualan', 'path' => '/', 'permission' => 'lihat_dashboard'],
                ],
            ],
            [
                'name' => 'Manajemen Akses',
                'icon' => 'manajemen-akses',
                'subItems' => [
                    ['name' => 'Kelola Role', 'path' => '/roles', 'permission' => 'lihat_role'],
                    ['name' => 'Kelola Pengguna', 'path' => '/users', 'permission' => 'lihat_user'],
                    ['name' => 'Kelola Pegawai', 'path' => '/pegawai', 'permission' => 'lihat_pegawai'],
                ],
            ],
            [
                'name' => 'Data Master',
                'icon' => 'data-master',
                'subItems' => [
                    ['name' => 'Kategori Produk', 'path' => '/kategori-produk', 'permission' => 'lihat_kategori_produk'],
                    ['name' => 'Produk', 'path' => '/produk', 'permission' => 'lihat_produk'],
                    ['name' => 'Bundling', 'path' => '/bundling', 'permission' => 'lihat_bundling'],
                    ['name' => 'Satuan', 'path' => '/satuan', 'permission' => 'lihat_satuan'],
                    ['name' => 'Bahan Baku', 'path' => '/bahan-baku', 'permission' => 'lihat_stok'],
                ]
            ],
            [
                'name' => 'Manajemen Produk',
                'icon' => 'box',
                'subItems' => [
                    ['name' => 'Transaksi Bahan Baku', 'path' => '/transaksi-bahan-baku', 'permission' => 'lihat_stok'],
                    ['name' => 'Produksi', 'path' => '/produksi', 'permission' => 'lihat_produksi'],
                    ['name' => 'Promo Produk', 'path' => '/promo', 'permission' => 'lihat_promo'],
                ],
            ],
            [
                'icon' => 'ecommerce',
                'name' => 'Transaksi',
                'subItems' => [
                    ['name' => 'Pengaturan Member', 'path' => '/pengaturan-member', 'permission' => 'lihat_member'],
                    ['name' => 'Member', 'path' => '/member', 'permission' => 'lihat_member'],
                    ['name' => 'Kasir', 'path' => '/transaksi', 'permission' => 'lihat_transaksi'],
                ],
            ],
            [
                'name' => 'Laporan',
                'icon' => 'laporan',
                'subItems' => [
                    ['name' => 'Histori Transaksi', 'path' => '/histori-transaksi', 'permission' => 'lihat_laporan'],
                    ['name' => 'Laporan Penjualan', 'path' => '/laporan-penjualan', 'permission' => 'lihat_laporan'],
                    ['name' => 'Activity Log', 'path' => '/activity-Log', 'permission' => 'lihat_activity_log'],
                ],
            ],
        ];
    }

    public static function getOthersItems()
    {
        return [];
    }

    /**
     * Filter menu berdasarkan permission user yang sedang login.
     * Sub-item tanpa key 'permission' dianggap boleh diakses semua orang yang login.
     */
    protected static function filterByPermission(array $items)
    {
        $user = auth()->user();

        return collect($items)
            ->map(function ($item) use ($user) {
                if (isset($item['subItems'])) {
                    $item['subItems'] = collect($item['subItems'])
                        ->filter(fn($sub) => !isset($sub['permission']) || $user->can($sub['permission']))
                        ->values()
                        ->all();
                }
                return $item;
            })
            ->filter(function ($item) {
                // buang menu group yang subItems-nya jadi kosong semua
                return !isset($item['subItems']) || count($item['subItems']) > 0;
            })
            ->values()
            ->all();
    }

    public static function getMenuGroups()
    {
        return [
            [
                'title' => 'Menu',
                'items' => self::filterByPermission(self::getMainNavItems()),
            ],
        ];
    }

    public static function isActive($path)
    {
        return request()->is(ltrim($path, '/'));
    }

    public static function getIconSvg($iconName)
    {
        $icons = [
            'dashboard' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M5.5 3.25C4.25736 3.25 3.25 4.25736 3.25 5.5V8.99998C3.25 10.2426 4.25736 11.25 5.5 11.25H9C10.2426 11.25 11.25 10.2426 11.25 8.99998V5.5C11.25 4.25736 10.2426 3.25 9 3.25H5.5ZM4.75 5.5C4.75 5.08579 5.08579 4.75 5.5 4.75H9C9.41421 4.75 9.75 5.08579 9.75 5.5V8.99998C9.75 9.41419 9.41421 9.74998 9 9.74998H5.5C5.08579 9.74998 4.75 9.41419 4.75 8.99998V5.5ZM5.5 12.75C4.25736 12.75 3.25 13.7574 3.25 15V18.5C3.25 19.7426 4.25736 20.75 5.5 20.75H9C10.2426 20.75 11.25 19.7427 11.25 18.5V15C11.25 13.7574 10.2426 12.75 9 12.75H5.5ZM4.75 15C4.75 14.5858 5.08579 14.25 5.5 14.25H9C9.41421 14.25 9.75 14.5858 9.75 15V18.5C9.75 18.9142 9.41421 19.25 9 19.25H5.5C5.08579 19.25 4.75 18.9142 4.75 18.5V15ZM12.75 5.5C12.75 4.25736 13.7574 3.25 15 3.25H18.5C19.7426 3.25 20.75 4.25736 20.75 5.5V8.99998C20.75 10.2426 19.7426 11.25 18.5 11.25H15C13.7574 11.25 12.75 10.2426 12.75 8.99998V5.5ZM15 4.75C14.5858 4.75 14.25 5.08579 14.25 5.5V8.99998C14.25 9.41419 14.5858 9.74998 15 9.74998H18.5C18.9142 9.74998 19.25 9.41419 19.25 8.99998V5.5C19.25 5.08579 18.9142 4.75 18.5 4.75H15ZM15 12.75C13.7574 12.75 12.75 13.7574 12.75 15V18.5C12.75 19.7426 13.7574 20.75 15 20.75H18.5C19.7426 20.75 20.75 19.7427 20.75 18.5V15C20.75 13.7574 19.7426 12.75 18.5 12.75H15ZM14.25 15C14.25 14.5858 14.5858 14.25 15 14.25H18.5C18.9142 14.25 19.25 14.5858 19.25 15V18.5C19.25 18.9142 18.9142 19.25 18.5 19.25H15C14.5858 19.25 14.25 18.9142 14.25 18.5V15Z" fill="currentColor"></path></svg>',
            'data-master' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"> <ellipse cx="12" cy="5" rx="8" ry="3"/> <path d="M4 5v7c0 1.66 3.58 3 8 3s8-1.34 8-3V5"/> <path d="M4 12v7c0 1.66 3.58 3 8 3s8-1.34 8-3v-7"/> </svg>',
            'manajemen-akses' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 11 2 2 4-4"/></svg>',
            'ecommerce' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>',
            'laporan' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"/><path d="M22 12A10 10 0 0 0 12 2v10z"/></svg>',
            'box' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"> <path d="M21 16V8a2 2 0 0 0-1-1.73L13 2.27a2 2 0 0 0-2 0L4 6.27A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/> <path d="M3.3 7L12 12l8.7-5"/> <path d="M12 22V12"/> </svg>',
        ];

        return $icons[$iconName] ?? '<svg width="1em" height="1em" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" fill="currentColor"/></svg>';
    }
}