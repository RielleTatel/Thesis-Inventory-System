<x-app-shell area="Administration" role="Administrator" :home="route('admin.accounts.index')" :title="$title ?? null">
    <x-slot:nav>
        <x-nav-item :href="route('admin.accounts.index')" :active="request()->routeIs('admin.accounts.*')">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            Department accounts
        </x-nav-item>

        <x-nav-item :href="route('admin.activity-log.index')" :active="request()->routeIs('admin.activity-log.*')">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
            </svg>
            Activity log
        </x-nav-item>
    </x-slot:nav>

    {{ $slot }}
</x-app-shell>
