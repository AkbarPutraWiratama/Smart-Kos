<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;

class SmartKosMenuHelper
{
    /**
     * Get menu groups based on authenticated user's role or guest status.
     */
    public static function getMenuGroups(): array
    {
        $user = Auth::user();

        if (! $user) {
            return self::getGuestMenu();
        }

        return match ($user->role) {
            'admin' => self::getAdminMenu(),
            'staff' => self::getStaffMenu(),
            'penyewa' => self::getPenyewaMenu(),
            default => self::getGuestMenu(),
        };
    }

    /**
     * Guest Navigation:
     * - Landing Page
     * - Lokasi & Kamar
     * - Login
     */
    public static function getGuestMenu(): array
    {
        return [
            [
                'title' => 'Navigasi',
                'items' => [
                    [
                        'icon' => 'pages',
                        'name' => 'Landing Page',
                        'path' => '/',
                    ],
                    [
                        'icon' => 'tables',
                        'name' => 'Lokasi & Kamar',
                        'path' => '/rooms',
                    ],
                    [
                        'icon' => 'authentication',
                        'name' => 'Login',
                        'path' => '/login',
                    ],
                ],
            ],
        ];
    }

    /**
     * Penyewa Navigation:
     * - Dashboard
     * - Pembayaran
     * - Aduan
     * - Akun
     */
    public static function getPenyewaMenu(): array
    {
        return [
            [
                'title' => 'Menu Penyewa',
                'items' => [
                    [
                        'icon' => 'dashboard',
                        'name' => 'Dashboard',
                        'path' => '/penyewa/dashboard',
                    ],
                    [
                        'icon' => 'ecommerce',
                        'name' => 'Pembayaran',
                        'path' => '/penyewa/payments',
                    ],
                    [
                        'icon' => 'support-ticket',
                        'name' => 'Aduan',
                        'path' => '/penyewa/complaints',
                    ],
                    [
                        'icon' => 'user-profile',
                        'name' => 'Akun',
                        'path' => '/penyewa/account',
                    ],
                ],
            ],
        ];
    }

    /**
     * Staff Navigation:
     * - Dashboard
     * - Status Pembayaran Penyewa
     * - Tindakan Aduan
     * - Akun
     */
    public static function getStaffMenu(): array
    {
        return [
            [
                'title' => 'Menu Staff',
                'items' => [
                    [
                        'icon' => 'dashboard',
                        'name' => 'Dashboard',
                        'path' => '/staff/dashboard',
                    ],
                    [
                        'icon' => 'tables',
                        'name' => 'Status Pembayaran Penyewa',
                        'path' => '/staff/payments',
                    ],
                    [
                        'icon' => 'support-ticket',
                        'name' => 'Tindakan Aduan',
                        'path' => '/staff/complaints',
                    ],
                    [
                        'icon' => 'user-profile',
                        'name' => 'Akun',
                        'path' => '/staff/account',
                    ],
                ],
            ],
        ];
    }

    /**
     * Admin Navigation:
     * - Dashboard
     * - Status Penyewa
     * - Tindakan Aduan
     * - Keuangan
     * - Lokasi & Kamar
     * - Pelanggan & Staff
     * - Akun
     */
    public static function getAdminMenu(): array
    {
        return [
            [
                'title' => 'Menu Utama',
                'items' => [
                    [
                        'icon' => 'dashboard',
                        'name' => 'Dashboard',
                        'path' => '/admin/dashboard',
                    ],
                    [
                        'icon' => 'tables',
                        'name' => 'Status Penyewa',
                        'path' => '/admin/tenants',
                    ],
                    [
                        'icon' => 'support-ticket',
                        'name' => 'Tindakan Aduan',
                        'path' => '/admin/complaints',
                    ],
                    [
                        'icon' => 'charts',
                        'name' => 'Keuangan',
                        'path' => '/admin/finance',
                    ],
                ],
            ],
            [
                'title' => 'Pengaturan & Master Data',
                'items' => [
                    [
                        'icon' => 'forms',
                        'name' => 'Lokasi & Kamar',
                        'path' => '/admin/locations',
                    ],
                    [
                        'icon' => 'user-profile',
                        'name' => 'Pelanggan & Staff',
                        'path' => '/admin/users',
                    ],
                    [
                        'icon' => 'ui-elements',
                        'name' => 'Akun',
                        'path' => '/admin/account',
                    ],
                ],
            ],
        ];
    }

    public static function getIconSvg(string $iconName): string
    {
        return MenuHelper::getIconSvg($iconName);
    }
}
