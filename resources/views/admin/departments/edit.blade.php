<x-admin-layout title="Edit department account">
    <div class="max-w-4xl mx-auto">
        <x-page-heading title="Edit department account" class="mb-6">
            Update the department details and its login email.
        </x-page-heading>

        @include('admin.departments._form', [
            'account' => $account,
            'action' => route('admin.accounts.update', $account),
            'method' => 'PUT',
        ])
    </div>
</x-admin-layout>
