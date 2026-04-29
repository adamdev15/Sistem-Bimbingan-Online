<?php

namespace App\View;

use Illuminate\Support\Facades\Auth;

class DashboardNavigation
{
    /**
     * @return list<array{label: string, icon: string, href?: string, active?: bool, children?: list<array{label: string, href: string, active: bool}>}>
     */
    public static function menuItems(): array
    {
        $user = Auth::user();
        if ($user === null) {
            return [];
        }

        $definitions = [
            [
                'label' => 'Dashboard',
                'icon' => 'dashboard',
                'route' => 'dashboard',
                'roles' => ['super_admin', 'admin_cabang', 'tutor', 'siswa'],
                'patterns' => ['dashboard'],
            ],
            [
                'label' => 'Master Data',
                'icon' => 'master',
                'roles' => ['super_admin', 'admin_cabang', 'tutor'],
                'children' => [
                    [
                        'label' => 'Cabang',
                        'route' => 'cabang.index',
                        'roles' => ['super_admin'],
                        'patterns' => ['cabang.*'],
                    ],
                    [
                        'label' => 'Siswa',
                        'route' => 'siswa.index',
                        'roles' => ['super_admin', 'admin_cabang'],
                        'patterns' => ['siswa.*'],
                    ],
                    [
                        'label' => 'Siswa (Tutor)',
                        'route' => 'tutor.siswa.index',
                        'roles' => ['tutor'],
                        'patterns' => ['tutor.siswa.*'],
                    ],
                    [
                        'label' => 'Tutor',
                        'route' => 'tutors.index',
                        'roles' => ['super_admin', 'admin_cabang'],
                        'patterns' => ['tutors.*'],
                    ],
                    [
                        'label' => 'Materi Les',
                        'route' => 'materi-les.index',
                        'roles' => ['super_admin', 'admin_cabang'],
                        'patterns' => ['materi-les.*'],
                    ],
                ]
            ],
            [
                'label' => 'Absensi',
                'icon' => 'presensi',
                'route' => 'presensi.index',
                'roles' => ['super_admin', 'admin_cabang', 'tutor', 'siswa'],
                'patterns' => ['presensi.*'],
            ],
            [
                'label' => 'Keuangan',
                'icon' => 'keuangan',
                'roles' => ['super_admin', 'admin_cabang', 'siswa'],
                'children' => [
                    [
                        'label' => 'Gaji Tutor',
                        'route' => 'salaries.index',
                        'roles' => ['super_admin', 'admin_cabang'],
                        'patterns' => ['salaries.*'],
                    ],
                    [
                        'label' => 'Biaya',
                        'route' => 'fees.index',
                        'roles' => ['super_admin'],
                        'patterns' => ['fees.*'],
                    ],
                    [
                        'label' => 'Pembayaran',
                        'route' => 'pembayaran.index',
                        'roles' => ['super_admin', 'admin_cabang', 'siswa'],
                        'patterns' => ['pembayaran.*'],
                    ],
                    [
                        'label' => 'Pengeluaran',
                        'route' => 'pengeluaran.index',
                        'roles' => ['super_admin', 'admin_cabang'],
                        'patterns' => ['pengeluaran.*'],
                    ],
                    
                    [
                        'label' => 'Laporan Keuangan',
                        'route' => 'laporan-keuangan.index',
                        'roles' => ['super_admin', 'admin_cabang'],
                        'patterns' => ['laporan-keuangan.*'],
                    ],
                ]
            ],
            [
                'label' => 'Pengaturan',
                'icon' => 'settings',
                'roles' => ['super_admin'],
                'children' => [
                    [
                        'label' => 'User Manajemen',
                        'route' => 'users.index',
                        'roles' => ['super_admin'],
                        'patterns' => ['users.*'],
                    ],
                    [
                        'label' => 'Notif Whatsapp',
                        'route' => 'whatsapp-settings.edit',
                        'roles' => ['super_admin'],
                        'patterns' => ['whatsapp-settings.*'],
                    ],
                    [
                        'label' => 'Pengaturan Website',
                        'route' => 'settings.website',
                        'roles' => ['super_admin'],
                        'patterns' => ['settings.landing*'],
                    ],
                ]
            ],
            [
                'label' => 'Profil Akun',
                'icon' => 'profile',
                'route' => 'profile.edit',
                'roles' => ['super_admin', 'admin_cabang', 'tutor', 'siswa'],
                'patterns' => ['profile.*'],
            ],
        ];

        $items = [];
        foreach ($definitions as $def) {
            if (! $user->hasAnyRole($def['roles'])) {
                continue;
            }

            if (isset($def['children'])) {
                foreach ($def['children'] as $child) {
                    if (! $user->hasAnyRole($child['roles'])) continue;
                    
                    // Assign specific icons based on label if not explicitly provided
                    $icon = match($child['label']) {
                        'Cabang' => 'Cabang',
                        'Siswa', 'Siswa (Tutor)' => 'Siswa',
                        'Tutor' => 'Tutor',
                        'Materi Les' => 'Materi Les',
                        'Biaya' => 'Biaya',
                        'Pembayaran' => 'Pembayaran',
                        'Pengeluaran' => 'Pengeluaran',
                        'Gaji Tutor' => 'Gaji Tutor',
                        'Laporan Keuangan' => 'Laporan Keuangan',
                        'User Manajemen' => 'Pengguna',
                        'Notif Whatsapp' => 'Whatsapp',
                        'Landing Page' => 'Pengaturan Landing',
                        default => $def['icon']
                    };

                    $items[] = [
                        'label' => $child['label'],
                        'icon' => $icon,
                        'href' => route($child['route']),
                        'active' => request()->routeIs($child['patterns']),
                    ];
                }
            } else {
                $items[] = [
                    'label' => $def['label'],
                    'icon' => $def['icon'],
                    'href' => route($def['route']),
                    'active' => request()->routeIs($def['patterns']),
                ];
            }
        }

        return $items;
    }

    public static function roleBadgeLabel(): string
    {
        $user = Auth::user();
        if ($user === null) {
            return '';
        }

        $name = $user->getRoleNames()->first();

        return match ($name) {
            'super_admin' => 'Admin Pusat',
            'admin_cabang' => 'Admin Cabang',
            'tutor' => 'Tutor',
            'siswa' => 'Siswa',
            default => 'Pengguna',
        };
    }
}
