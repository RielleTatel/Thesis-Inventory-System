<x-guest-layout>
    {{--
        Admin-mediated reset notice. This system has no email infrastructure, so
        there is no self-service "email me a reset link" flow — the CITS team /
        system administrator resets passwords on request (the admin-side reset).
        EDIT the contact details below to your real CITS contact (email / office).
    --}}
    <div class="text-center">
        <div class="mx-auto mb-5 grid h-14 w-14 place-items-center rounded-full bg-input text-navy">
            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
        </div>

        <h2 class="text-xl font-bold text-navy">Forgot your password?</h2>

        <p class="mt-3 text-sm leading-relaxed text-text/70">
            No problem — password resets here are handled by the CITS team. Contact
            your system administrator and they'll set a new password for you, then
            share it so you can sign in and change it.
        </p>
    </div>

    {{-- Contact details — placeholder values, edit to your real CITS contact. --}}
    <div class="mt-6 rounded-xl bg-input px-5 py-4 text-left">
        <p class="text-xs font-bold uppercase tracking-wide text-text/50">How to reach CITS</p>
        <ul class="mt-3 space-y-3 text-sm text-text">
            <li class="flex items-start gap-3">
                <span class="mt-0.5 shrink-0 text-navy">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/>
                    </svg>
                </span>
                <span>Email CITS at
                    <a href="mailto:cits@adzu.edu.ph" class="font-semibold text-navy hover:text-navy/80 transition">cits@adzu.edu.ph</a>
                </span>
            </li>
            <li class="flex items-start gap-3">
                <span class="mt-0.5 shrink-0 text-navy">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
                    </svg>
                </span>
                <span>Or visit the CITS office during office hours</span>
            </li>
        </ul>
    </div>

    <x-btn href="{{ route('login') }}" variant="accent" class="mt-6 w-full rounded-full py-3 text-base font-bold">
        Back to sign in
    </x-btn>
</x-guest-layout>
