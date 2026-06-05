<x-department-layout title="Edit thesis">
    <div class="max-w-4xl mx-auto">
        <x-page-heading title="Edit thesis" class="mb-6">
            Update this record's descriptive information.
        </x-page-heading>

        @include('user.thesis._form', [
            'thesis' => $thesis,
            'action' => route('department.theses.update', $thesis),
            'method' => 'PUT',
        ])
    </div>
</x-department-layout>
