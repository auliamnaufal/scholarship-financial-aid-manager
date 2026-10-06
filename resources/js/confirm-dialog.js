// Any form carrying data-confirm-title asks for confirmation in a dialog before
// it is sent. The dialog itself lives in components/confirm-dialog.blade.php.
//
//   <form data-confirm-title="Tarik pendaftaran?"
//         data-confirm-message="Tindakan ini tidak dapat dibatalkan."
//         data-confirm-label="Ya, tarik" data-confirm-tone="danger">
//
// {field} in the message is replaced by that field's value in the form, so a
// dialog can repeat what was typed ("Rp {awarded_amount}").

function fill(template, form) {
    return template.replace(/\{(\w+)\}/g, (placeholder, name) => {
        const field = form.elements[name];

        if (!field || field.value === '') {
            return placeholder;
        }

        const number = Number(field.value);

        return Number.isFinite(number) ? number.toLocaleString('id-ID') : field.value;
    });
}

document.addEventListener('submit', (event) => {
    const form = event.target;

    if (!(form instanceof HTMLFormElement) || !form.dataset.confirmTitle) {
        return;
    }

    // The dialog sets this just before it re-submits the form for real.
    if (form.dataset.confirmed === '1') {
        return;
    }

    event.preventDefault();

    window.dispatchEvent(
        new CustomEvent('open-confirm', {
            detail: {
                form,
                title: form.dataset.confirmTitle,
                message: fill(form.dataset.confirmMessage ?? '', form),
                label: form.dataset.confirmLabel,
                tone: form.dataset.confirmTone,
            },
        }),
    );
});
