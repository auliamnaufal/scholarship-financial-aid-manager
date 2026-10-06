@props(['name', 'label', 'options', 'selected' => null])

{{-- A labelled dropdown for the lists' filter bar; "All" means no filter. --}}
<select name="{{ $name }}" aria-label="{{ $label }}" onchange="this.form.submit()"
    class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    <option value="">{{ $label }}: {{ __('All') }}</option>
    @foreach ($options as $value => $text)
        <option value="{{ $value }}" @selected((string) $selected === (string) $value)>{{ $text }}</option>
    @endforeach
</select>
