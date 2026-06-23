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
    </form>
</x-guest-layout>
