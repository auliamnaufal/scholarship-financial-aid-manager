@php
    $disbursement = $disbursement ?? null;
@endphp

<div>
    <x-input-label for="seq_no" :value="__('Sequence #')" />
    <x-text-input id="seq_no" name="seq_no" type="number" min="1" class="mt-1 block w-full" :value="old('seq_no', $disbursement?->seq_no)" required />
    <x-input-error :messages="$errors->get('seq_no')" class="mt-2" />
</div>
<div>
    <x-input-label for="amount" :value="__('Amount')" />
    <x-text-input id="amount" name="amount" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('amount', $disbursement?->amount)" required />
    <x-input-error :messages="$errors->get('amount')" class="mt-2" />
</div>
<div>
    <x-input-label for="disbursement_date" :value="__('Date')" />
    <x-text-input id="disbursement_date" name="disbursement_date" type="date" class="mt-1 block w-full" :value="old('disbursement_date', $disbursement?->disbursement_date?->format('Y-m-d'))" required />
    <x-input-error :messages="$errors->get('disbursement_date')" class="mt-2" />
</div>
<div>
    <x-input-label for="semester" :value="__('Semester')" />
    <select id="semester" name="semester" class="mt-1 block w-full border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm" required>
        @php $chosen = old('semester', $disbursement?->semester ?? $application->semester); @endphp
        @foreach (\App\Support\Semester::options(include: $chosen) as $value => $label)
            <option value="{{ $value }}" @selected((string) $chosen === (string) $value)>{{ $label }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('semester')" class="mt-2" />
</div>
