<x-layouts.dashboard-shell title="Profil">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Profil akun</h1>
        <p class="mt-1 text-sm text-slate-600">Kelola informasi profil, kata sandi, dan opsi penghapusan akun.</p>
    </div>

    <div class="max-w-3xl space-y-6">
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            @include('profile.partials.update-profile-information-form')
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            @include('profile.partials.update-password-form')
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</x-layouts.dashboard-shell>
