<x-department-layout title="Add thesis">
    <div class="max-w-4xl mx-auto">
        <x-page-heading title="Add thesis" class="mb-6">
            Catalog a new thesis record. Fields marked <span class="text-danger">*</span> are required.
        </x-page-heading>

        @include('user.thesis._form', [
            'thesis' => null,
            'action' => route('department.theses.store'),
            'method' => 'POST',
        ])
    </div>
</x-department-layout>
