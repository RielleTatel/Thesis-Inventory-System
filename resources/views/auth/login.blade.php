<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email or username -->
        <div>
            <x-input-label for="email" :value="__('Email or username')" />
            <x-text-input
                id="email"
                class="mt-2 block w-full rounded-lg border-navy/30 bg-surface px-4 py-3 focus:border-navy focus:ring-navy"
                type="email"
                name="email"
                :value="old('email')"
                placeholder="department@univ.edu"
                required
                autofocus
                autocomplete="username"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-5">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input
                id="password"
                class="mt-2 block w-full rounded-lg border-transparent bg-input px-4 py-3 focus:border-navy focus:ring-navy"
                type="password"
                name="password"
                placeholder="••••••••"
                required
                autocomplete="current-password"
            />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Forgot password -->
        @if (Route::has('password.request'))
            <div class="mt-3 text-right">
                <a class="text-sm font-semibold text-navy hover:text-navy/80 transition" href="{{ route('password.request') }}">
                    {{ __('Forgot password?') }}
                </a>
            </div>
        @endif

        <!-- Login button -->
        <div class="mt-6">
            <x-btn variant="accent" class="w-full rounded-full py-3 text-base font-bold">
                {{ __('Login') }}
            </x-btn>
        </div>

        <!-- OR divider -->
        <div class="relative mt-6 mb-6">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-200"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="bg-surface px-4 text-gray-400">OR</span>
            </div>
        </div>

        <!-- Google sign-in (placeholder — no route wired yet) -->
        <button type="button" class="w-full inline-flex items-center justify-center gap-3 rounded-full border border-gray-200 bg-surface px-4 py-3 text-sm font-medium text-text shadow-sm hover:bg-gray-50 transition">
            <svg class="h-5 w-5" viewBox="0 0 24 24">
                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/>
                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
            </svg>
            Sign in with Google
        </button>
    </form>
</x-guest-layout>
