<?php

namespace App\View;

use Illuminate\Support\Facades\Auth;

final class DashboardNavigation
{
    /**
     * @return list<array{label: string, href: string, active: bool}>
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
                'route' => 'dashboard',
                'roles' => ['super_admin', 'admin_cabang', 'tutor', 'siswa'],
                'patterns' => ['dashboard'],
            ],
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
                'label' => 'Siswa',
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
                'label' => 'Mata pelajaran',
                'route' => 'mata-pelajaran.index',
                'roles' => ['super_admin'],
                'patterns' => ['mata-pelajaran.*'],
            ],
            [
                'label' => 'Gaji tutor',
                'route' => 'salaries.index',
                'roles' => ['super_admin', 'admin_cabang'],
                'patterns' => ['salaries.*'],
            ],
            [
                'label' => 'Jadwal',
                'route' => 'jadwal.index',
                'roles' => ['super_admin', 'admin_cabang', 'tutor', 'siswa'],
                'patterns' => ['jadwal.*'],
            ],
            [
                'label' => 'Presensi',
                'route' => 'presensi.index',
                'roles' => ['super_admin', 'admin_cabang', 'tutor', 'siswa'],
                'patterns' => ['presensi.*'],
            ],
            [
                'label' => 'Pembayaran',
                'route' => 'pembayaran.index',
                'roles' => ['super_admin', 'admin_cabang', 'siswa'],
                'patterns' => ['pembayaran.*'],
            ],
            [
                'label' => 'Laporan',
                'route' => 'laporan.index',
                'roles' => ['super_admin', 'admin_cabang'],
                'patterns' => ['laporan.*'],
            ],
            [
                'label' => 'Profil',
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
            $items[] = [
                'label' => $def['label'],
                'href' => route($def['route']),
                'active' => request()->routeIs($def['patterns']),
            ];
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
            'super_admin' => 'Super Admin',
            'admin_cabang' => 'Admin Cabang',
            'tutor' => 'Tutor',
            'siswa' => 'Siswa',
            default => 'Pengguna',
        };
    }
}
