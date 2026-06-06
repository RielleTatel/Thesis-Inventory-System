<x-app-shell area="Department" role="Department" :home="route('department.theses.index')" :title="$title ?? null">
    <x-slot:nav>
        <x-nav-item :href="route('department.theses.index')" :active="request()->routeIs('department.theses.index')">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
                <rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
            </svg>
            My theses
        </x-nav-item>

        <x-nav-item :href="route('department.theses.create')" :active="request()->routeIs('department.theses.create')">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M12 5v14M5 12h14"/>
            </svg>
            Add thesis
        </x-nav-item>
    </x-slot:nav>

    {{ $slot }}
</x-app-shell>
