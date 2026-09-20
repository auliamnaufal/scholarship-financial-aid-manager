<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Apply to') }} {{ $program->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm ring-1 ring-slate-900/5 rounded-xl p-6">
                <form method="POST" action="{{ route('student.applications.store') }}">
                    @csrf
                    <input type="hidden" name="program_id" value="{{ $program->id }}">

                    <div>
                        <x-input-label for="semester" :value="__('Semester')" />
                        <x-text-input id="semester" name="semester" type="text" class="mt-1 block w-full"
                            placeholder="e.g. 2026-1" :value="old('semester')" required autofocus />
                        <x-input-error :messages="$errors->get('semester')" class="mt-2" />
                        <x-input-error :messages="$errors->get('program_id')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end mt-6 gap-3">
                        <a href="{{ route('home') }}" class="text-gray-600 hover:underline">{{ __('Cancel') }}</a>
                        <x-primary-button>{{ __('Submit Application') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
