@php
    $user = auth()->user();
    $dashTitle = 'Dashboard — eBimbel';
    if ($user->hasRole('super_admin')) {
        $dashTitle = 'Super Admin — eBimbel';
    } elseif ($user->hasRole('admin_cabang')) {
        $dashTitle = 'Admin Cabang — eBimbel';
    } elseif ($user->hasRole('tutor')) {
        $dashTitle = 'Beranda Tutor — eBimbel';
    } elseif ($user->hasRole('siswa')) {
        $dashTitle = 'Beranda Siswa — eBimbel';
    }
@endphp
<x-layouts.dashboard-shell :title="$dashTitle">
    @if ($user->hasRole('super_admin'))
        @include('dashboard.partials.operator')
    @elseif ($user->hasRole('admin_cabang'))
        @include('dashboard.partials.admin-cabang')
    @elseif ($user->hasRole('tutor'))
        @include('dashboard.partials.tutor-home')
    @else
        @include('dashboard.partials.siswa-home')
    @endif
</x-layouts.dashboard-shell>
