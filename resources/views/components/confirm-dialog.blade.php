{{-- One dialog for the whole page. Forms with data-confirm-title open it (see js/confirm-dialog.js). --}}
<div x-data="{
         open: false,
         form: null,
         title: '',
         message: '',
         label: 'Lanjutkan',
         tone: 'primary',
         tones: {
             danger: ['bg-red-100 text-red-600', 'bg-red-600 hover:bg-red-500 focus-visible:ring-red-500'],
             success: ['bg-emerald-100 text-emerald-600', 'bg-emerald-600 hover:bg-emerald-500 focus-visible:ring-emerald-500'],
             primary: ['bg-indigo-100 text-indigo-700', 'bg-indigo-700 hover:bg-indigo-600 focus-visible:ring-indigo-500'],
         },
         show(detail) {
             this.form = detail.form;
             this.title = detail.title;
             this.message = detail.message;
             this.label = detail.label || 'Lanjutkan';
             this.tone = this.tones[detail.tone] ? detail.tone : 'primary';
             this.open = true;
             this.$nextTick(() => this.$refs.cancel.focus());
         },
         confirm() {
             const form = this.form;
             this.open = false;
             form.dataset.confirmed = '1';
             form.requestSubmit();
             delete form.dataset.confirmed;
         },
     }"
     @open-confirm.window="show($event.detail)"
     @keydown.escape.window="open = false"
     x-show="open"
     x-cloak
     class="fixed inset-0 z-[110] flex items-center justify-center p-4"
     role="dialog"
     aria-modal="true"
     :aria-label="title">
    <div x-show="open" x-transition.opacity.duration.200ms class="absolute inset-0 bg-slate-900/60" @click="open = false"></div>

    <div x-show="open"
         x-transition:enter="transition duration-200 ease-out"
         x-transition:enter-start="translate-y-2 scale-95 opacity-0"
         x-transition:enter-end="translate-y-0 scale-100 opacity-100"
         x-transition:leave="transition duration-150 ease-in"
         x-transition:leave-start="scale-100 opacity-100"
         x-transition:leave-end="scale-95 opacity-0"
         class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
        <div class="flex items-start gap-4">
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full" :class="tones[tone][0]">
                <svg x-show="tone === 'danger'" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
                <svg x-show="tone !== 'danger'" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" /></svg>
            </span>
            <div class="min-w-0">
                <h3 class="font-display text-lg font-bold text-slate-900" x-text="title"></h3>
                <p class="mt-2 text-sm leading-relaxed text-slate-600" x-text="message"></p>
            </div>
        </div>

        <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <button type="button" x-ref="cancel" @click="open = false"
                    class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">
                {{ __('Cancel') }}
            </button>
            <button type="button" @click="confirm()" x-text="label"
                    class="rounded-lg px-4 py-2 text-sm font-semibold text-white shadow-sm transition focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2"
                    :class="tones[tone][1]"></button>
        </div>
    </div>
</div>
