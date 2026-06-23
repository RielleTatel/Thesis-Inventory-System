<x-admin-layout title="Department accounts">
    @php
        // Reopen the reset modal with its errors if validation failed (passwords
        // are never flashed, but the account context carries through old input).
        $resetInitial = ($errors->has('password') && old('account_id'))
            ? [
                'name' => old('account_name'),
                'email' => old('account_email'),
                'action' => route('admin.accounts.reset-password', old('account_id')),
            ]
            : null;
    @endphp

    <div x-data="{ confirm: null, reset: @js($resetInitial) }">
        {{-- Shown once after a reset so the admin can copy and relay the new
             password out-of-band (no email infra). Persists until dismissed. --}}
        @if ($creds = session('reset_password'))
            <div x-data="{ show: true }" x-show="show" x-cloak
                 class="mb-6 flex items-start gap-3 rounded-lg border-l-4 border-green bg-surface p-4 shadow-sm">
                <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-green/10 text-green">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M20 6 9 17l-5-5"/>
                    </svg>
                </span>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-text">New password for {{ $creds['name'] }}</p>
                    <p class="mt-0.5 text-xs text-text/60">Shown once — copy it now and relay it to the department, then dismiss.</p>
                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <code class="select-all rounded-md bg-input px-3 py-1.5 text-sm font-semibold text-navy">{{ $creds['password'] }}</code>
                        <span class="text-xs text-text/50">{{ $creds['email'] }}</span>
                    </div>
                </div>
                <button type="button" @click="show = false" aria-label="Dismiss"
                        class="shrink-0 rounded text-text/40 transition hover:text-text focus:outline-none focus:ring-2 focus:ring-cyan">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M18 6 6 18M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        @endif

        <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
            <x-page-heading title="Department accounts">
                Create and manage the department logins that can catalog theses.
            </x-page-heading>
            <x-btn href="{{ route('admin.accounts.create') }}" variant="accent">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 5v14M5 12h14"/>
                </svg>
                Create department account
            </x-btn>
        </div>

        <x-card>
            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                <h2 class="text-lg font-semibold text-navy">{{ $accounts->total() }} {{ Str::plural('account', $accounts->total()) }}</h2>
                <form method="GET" action="{{ route('admin.accounts.index') }}" class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-text/40">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                        </svg>
                    </span>
                    <input type="search" name="q" value="{{ $search }}" placeholder="Search accounts…"
                           class="w-64 max-w-full rounded-md border-0 bg-input py-2 pl-9 pr-3 text-sm text-text placeholder:text-text/40 focus:ring-2 focus:ring-cyan">
                </form>
            </div>

            @if ($accounts->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs font-bold uppercase tracking-wide text-text/50 border-b border-text/10">
                                <th class="py-3 pr-4">Department</th>
                                <th class="py-3 pr-4">Username / email</th>
                                <th class="py-3 pr-4">Status</th>
                                <th class="py-3 pr-4">Records</th>
                                <th class="py-3 pr-4">Created</th>
                                <th class="py-3 pl-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($accounts as $account)
                                @php
                                    $login = $account->users->first();
                                    $active = $login && $login->is_active;
                                @endphp
                                <tr class="border-b border-text/10 last:border-0">
                                    <td class="py-4 pr-4 font-semibold text-navy">{{ $account->name }}</td>
                                    <td class="py-4 pr-4 text-text/70">{{ $login?->email ?? '—' }}</td>
                                    <td class="py-4 pr-4">
                                        @if ($active)
                                            <x-badge tone="green"><span class="w-1.5 h-1.5 rounded-full bg-green mr-1.5"></span>Active</x-badge>
                                        @else
                                            <x-badge tone="gray"><span class="w-1.5 h-1.5 rounded-full bg-text/40 mr-1.5"></span>Inactive</x-badge>
                                        @endif
                                    </td>
                                    <td class="py-4 pr-4 text-text/70">{{ $account->theses_count }}</td>
                                    <td class="py-4 pr-4 text-text/70 whitespace-nowrap">{{ $account->created_at?->format('Y-m-d') }}</td>
                                    <td class="py-4 pl-4">
                                        <div class="flex items-center justify-end gap-1.5">
                                            @if ($login)
                                                <a href="{{ route('admin.accounts.edit', $account) }}"
                                                   class="rounded-md border border-text/15 bg-surface px-3 py-1.5 text-xs font-semibold text-text hover:bg-bg hover:border-text/30 transition">Edit</a>

                                                <button type="button"
                                                        @click="reset = { name: @js($account->name), email: @js($login->email), action: '{{ route('admin.accounts.reset-password', $account) }}', id: {{ $account->id }} }"
                                                        class="rounded-md border border-text/15 bg-surface px-3 py-1.5 text-xs font-semibold text-text hover:bg-bg hover:border-text/30 transition">Reset password</button>

                                                <form method="POST" action="{{ route('admin.accounts.toggle', $account) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit"
                                                            class="grid place-items-center w-8 h-8 rounded-md border border-text/10 text-text/60 hover:text-navy hover:border-navy transition"
                                                            title="{{ $active ? 'Deactivate' : 'Activate' }}">
                                                        @if ($active)
                                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                                <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                                            </svg>
                                                        @else
                                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                                <path d="M20 6 9 17l-5-5"/>
                                                            </svg>
                                                        @endif
                                                    </button>
                                                </form>
                                            @endif

                                            <button type="button"
                                                    @click="confirm = { name: @js($account->name), email: @js($login?->email ?? '—'), records: {{ $account->theses_count }}, action: '{{ route('admin.accounts.destroy', $account) }}' }"
                                                    class="grid place-items-center w-8 h-8 rounded-md border border-text/10 text-text/60 hover:text-danger hover:border-danger transition"
                                                    aria-label="Delete">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                    <path d="M3 6h18M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2m2 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">{{ $accounts->links() }}</div>
            @else
                <div class="py-12 text-center">
                    <p class="text-sm font-semibold text-navy">No department accounts</p>
                    <p class="mt-1 text-sm text-text/60">
                        {{ $search !== '' ? 'No accounts match your search.' : 'Create the first department account to get started.' }}
                    </p>
                </div>
            @endif
        </x-card>

        {{-- Delete with the FR-2.3 keep-or-delete choice --}}
        <div x-show="confirm" x-cloak @keydown.escape.window="confirm = null"
             class="fixed inset-0 z-30 grid place-items-center bg-text/40 p-4" style="display:none">
            <div class="bg-surface rounded-lg shadow-lg max-w-lg w-full p-6" @click.outside="confirm = null">
                <h3 class="text-lg font-semibold text-navy">Delete department account</h3>
                <p class="mt-2 text-sm text-text/70 leading-relaxed">
                    You're about to delete the account for
                    “<span class="font-semibold text-text" x-text="confirm?.name"></span>”
                    (<span x-text="confirm?.email"></span>).
                </p>

                <div class="mt-4 flex items-start gap-2 rounded-md border border-danger/30 bg-danger/5 px-4 py-3 text-sm text-danger">
                    <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M4 5a2 2 0 0 1 2-2h12v16H6a2 2 0 0 0-2 2z"/><path d="M9 7h6M9 10h6"/>
                    </svg>
                    <span>
                        This department owns <span class="font-bold" x-text="confirm?.records"></span>
                        thesis record(s). Choose what happens to them.
                    </span>
                </div>

                <div class="mt-4 space-y-3">
                    <div class="rounded-md border border-text/15 px-4 py-3">
                        <p class="font-bold text-navy">Keep the records</p>
                        <p class="mt-0.5 text-sm text-text/60 leading-relaxed">
                            The records stay in the public catalog, still owned by this department. Only the
                            login is removed, so the account can no longer manage them.
                        </p>
                    </div>
                    <div class="rounded-md border border-danger/30 bg-danger/5 px-4 py-3">
                        <p class="font-bold text-danger">Delete the records too</p>
                        <p class="mt-0.5 text-sm text-danger/80 leading-relaxed">
                            Permanently removes the account and all of its thesis records from the archive.
                            This cannot be undone.
                        </p>
                    </div>
                </div>

                <form method="POST" :action="confirm?.action" class="mt-6 flex flex-wrap justify-end gap-3">
                    @csrf
                    @method('DELETE')
                    <x-btn type="button" variant="ghost" @click="confirm = null">Cancel</x-btn>
                    <x-btn type="submit" name="mode" value="keep" variant="primary">Keep the records</x-btn>
                    <x-btn type="submit" name="mode" value="delete" variant="danger">Delete the records too</x-btn>
                </form>
            </div>
        </div>

        {{-- Admin-mediated password reset — admin sets a new password and relays it. --}}
        <div x-show="reset" x-cloak @keydown.escape.window="reset = null"
             class="fixed inset-0 z-30 grid place-items-center bg-text/40 p-4" style="display:none">
            <div class="bg-surface rounded-lg shadow-lg max-w-md w-full p-6" @click.outside="reset = null">
                <h3 class="text-lg font-semibold text-navy">Reset department password</h3>
                <p class="mt-2 text-sm text-text/70 leading-relaxed">
                    Set a new password for
                    “<span class="font-semibold text-text" x-text="reset?.name"></span>”
                    (<span x-text="reset?.email"></span>). It's shown once after saving so you can relay it to the department.
                </p>

                <form method="POST" :action="reset?.action" class="mt-5 space-y-4">
                    @csrf
                    @method('PATCH')
                    {{-- Carried only so the modal can reopen with its errors on a failed reset. --}}
                    <input type="hidden" name="account_id" :value="reset?.id">
                    <input type="hidden" name="account_name" :value="reset?.name">
                    <input type="hidden" name="account_email" :value="reset?.email">

                    <div>
                        <label for="reset_password" class="block text-xs font-semibold text-text/60 mb-1">New password</label>
                        <input type="password" id="reset_password" name="password" autocomplete="new-password" required
                               class="w-full rounded-md border-0 bg-input py-2 px-3 text-sm text-text placeholder:text-text/40 focus:ring-2 focus:ring-cyan @error('password') ring-2 ring-danger @enderror">
                        @error('password')
                            <p class="mt-1 text-xs font-semibold text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="reset_password_confirmation" class="block text-xs font-semibold text-text/60 mb-1">Confirm new password</label>
                        <input type="password" id="reset_password_confirmation" name="password_confirmation" autocomplete="new-password" required
                               class="w-full rounded-md border-0 bg-input py-2 px-3 text-sm text-text placeholder:text-text/40 focus:ring-2 focus:ring-cyan">
                    </div>

                    <div class="flex justify-end gap-3 pt-1">
                        <x-btn type="button" variant="ghost" @click="reset = null">Cancel</x-btn>
                        <x-btn type="submit" variant="primary">Reset password</x-btn>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
