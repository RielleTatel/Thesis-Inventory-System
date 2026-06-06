@php
    $login = $account?->users->first();
    $vName = old('name', $account?->name);
    $vCode = old('code', $account?->code);
    $vEmail = old('email', $login?->email);

    $inputClass = 'w-full rounded-md border-0 bg-input text-sm text-text placeholder:text-text/40 focus:ring-2 focus:ring-cyan';
    $labelClass = 'block text-sm font-semibold text-text mb-1';
@endphp

<form method="POST" action="{{ $action }}">
    @csrf
    @if ($method === 'PUT')
        @method('PUT')
    @endif

    <x-card>
        <div class="space-y-6">
            <div>
                <label for="name" class="{{ $labelClass }}">Department name <span class="text-danger">*</span></label>
                <input type="text" id="name" name="name" value="{{ $vName }}"
                       placeholder="e.g. College of Computer Studies" class="{{ $inputClass }}">
                @error('name')<p class="mt-1 text-xs font-semibold text-danger">{{ $message }}</p>@enderror
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="code" class="{{ $labelClass }}">Department code <span class="text-danger">*</span></label>
                    <input type="text" id="code" name="code" value="{{ $vCode }}"
                           placeholder="e.g. CCS" class="{{ $inputClass }}">
                    @error('code')<p class="mt-1 text-xs font-semibold text-danger">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="email" class="{{ $labelClass }}">Login email <span class="text-danger">*</span></label>
                    <input type="email" id="email" name="email" value="{{ $vEmail }}"
                           placeholder="department@univ.edu" class="{{ $inputClass }}">
                    @error('email')<p class="mt-1 text-xs font-semibold text-danger">{{ $message }}</p>@enderror
                </div>
            </div>

            @unless ($account)
                <hr class="border-text/10">
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="password" class="{{ $labelClass }}">Password <span class="text-danger">*</span></label>
                        <input type="password" id="password" name="password" autocomplete="new-password" class="{{ $inputClass }}">
                        @error('password')<p class="mt-1 text-xs font-semibold text-danger">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="password_confirmation" class="{{ $labelClass }}">Confirm password <span class="text-danger">*</span></label>
                        <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" class="{{ $inputClass }}">
                    </div>
                </div>
                <p class="text-xs text-text/50">The department signs in with this email and password to manage its records.</p>
            @endunless
        </div>
    </x-card>

    <div class="mt-5 flex justify-end gap-3">
        <x-btn href="{{ route('admin.accounts.index') }}" variant="ghost">Cancel</x-btn>
        <x-btn type="submit" variant="accent">{{ $account ? 'Save changes' : 'Create account' }}</x-btn>
    </div>
</form>
