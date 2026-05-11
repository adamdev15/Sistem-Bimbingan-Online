<x-guest-layout title="Login - Bimbel Jarimatrik">
    <div class="mb-6">
        <h2 class="text-3xl font-bold text-blue-950">Masuk ke Akun</h2>
        <p class="mt-1 text-sm text-slate-500">Silakan login untuk mengakses dashboard bimbel.</p>
    </div>

    <x-auth-session-status class="mb-4 rounded-lg bg-green-50 px-3 py-2 text-green-700" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" class="text-slate-700" />
            <x-text-input id="email" class="mt-1 block w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="nama@email.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4" x-data="{ show: false }">
            <x-input-label for="password" :value="__('Password')" class="text-slate-700" />

            <div class="relative">
                <x-text-input id="password" class="block mt-1 w-full pr-10"
                                x-bind:type="show ? 'text' : 'password'"
                                name="password"
                                required autocomplete="current-password"
                                placeholder="Masukkan password" />
                
                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 transition-colors">
                    <!-- Eye Icon (Hide) -->
                    <svg x-show="!show" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <!-- Eye-off Icon (Show) -->
                    <svg x-show="show" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7 1.274-4.057 5.064-7 9.542-7 1.222 0 2.39.209 3.487.591M13.875 18.825L10 15M13.875 18.825l3.525 3.525M17.625 15L21 18.375M17.625 15l3.375-3.375M17.625 15l-3.375-3.375M10 15L6.625 18.375M10 15L6.625 11.625M10 15l3.875-3.825" />
                    </svg>
                </button>
            </div>

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="mt-6 flex items-center justify-between">
            @if (Route::has('password.request'))
                <a class="text-sm text-blue-700 hover:text-blue-800 hover:underline focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded-md" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="rounded-xl bg-blue-600 px-6 py-2.5 text-white hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
