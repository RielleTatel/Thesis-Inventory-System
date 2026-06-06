<x-admin-layout title="Create department account">
    <div class="max-w-4xl mx-auto">
        <x-page-heading title="Create department account" class="mb-6">
            Set up a new department and its login. Fields marked <span class="text-danger">*</span> are required.
        </x-page-heading>

        @include('admin.departments._form', [
            'account' => null,
            'action' => route('admin.accounts.store'),
            'method' => 'POST',
        ])
    </div>
</x-admin-layout>
