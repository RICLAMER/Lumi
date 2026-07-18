(() => {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const dialogs = new Map(
        [...document.querySelectorAll('dialog')].map((dialog) => [dialog.id, dialog])
    );

    const openDialog = (id) => {
        const dialog = dialogs.get(id);
        if (!dialog) return;
        if (typeof dialog.showModal === 'function') dialog.showModal();
        else dialog.setAttribute('open', '');
        dialog.querySelector('input:not([type="hidden"])')?.focus();
    };

    const closeDialog = (dialog) => {
        if (!dialog) return;
        if (typeof dialog.close === 'function') dialog.close();
        else dialog.removeAttribute('open');
    };

    document.querySelectorAll('[data-open-dialog]').forEach((button) => {
        button.addEventListener('click', () => openDialog(button.dataset.openDialog));
    });

    document.querySelectorAll('[data-close-dialog]').forEach((button) => {
        button.addEventListener('click', () => closeDialog(button.closest('dialog')));
    });

    document.querySelectorAll('[data-switch-dialog]').forEach((button) => {
        button.addEventListener('click', () => {
            closeDialog(button.closest('dialog'));
            openDialog(button.dataset.switchDialog);
        });
    });

    dialogs.forEach((dialog) => {
        dialog.addEventListener('click', (event) => {
            if (event.target === dialog) closeDialog(dialog);
        });
    });

    const postJson = async (url, payload) => {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrf,
            },
            body: JSON.stringify(payload),
        });
        const data = await response.json().catch(() => ({
            ok: false,
            message: 'A resposta do servidor não pôde ser lida.',
        }));
        if (!response.ok && !data.message) data.message = 'Não foi possível concluir agora.';
        return data;
    };

    const setSubmitting = (form, submitting) => {
        const submit = form.querySelector('[type="submit"]');
        if (!submit) return;
        if (!submit.dataset.label) submit.dataset.label = submit.textContent.trim();
        submit.disabled = submitting;
        submit.textContent = submitting ? 'Só um instante...' : submit.dataset.label;
    };

    const setMessage = (form, message, isSuccess = false) => {
        const target = form.querySelector('[data-form-message]');
        if (!target) return;
        target.textContent = message;
        target.classList.toggle('is-success', isSuccess);
    };

    const loginForm = document.querySelector('#login-form');
    loginForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (!loginForm.reportValidity()) return;
        setSubmitting(loginForm, true);
        setMessage(loginForm, '');
        const payload = Object.fromEntries(new FormData(loginForm));
        try {
            const data = await postJson('api/login.php', payload);
            if (data.ok) {
                window.location.assign(data.redirect || 'app.php');
                return;
            }
            setMessage(loginForm, data.message);
            const resend = loginForm.querySelector('[data-resend]');
            if (resend) resend.hidden = data.code !== 'email_unverified';
        } catch {
            setMessage(loginForm, 'Não consegui conectar agora. Verifique a internet e tente novamente.');
        } finally {
            setSubmitting(loginForm, false);
        }
    });

    loginForm?.querySelector('[data-resend]')?.addEventListener('click', async (event) => {
        const button = event.currentTarget;
        const email = loginForm.elements.email.value.trim();
        if (!email) {
            setMessage(loginForm, 'Informe o e-mail para reenviar a confirmação.');
            return;
        }
        button.disabled = true;
        const data = await postJson('api/resend-verification.php', { email });
        setMessage(loginForm, data.message, data.ok);
        button.disabled = false;
    });

    const registerForm = document.querySelector('#register-form');
    registerForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (!registerForm.reportValidity()) return;
        setSubmitting(registerForm, true);
        setMessage(registerForm, '');
        const formData = new FormData(registerForm);
        const payload = Object.fromEntries(formData);
        payload.consent = formData.has('consent');
        payload.age = Number(payload.age);

        try {
            const data = await postJson('api/register.php', payload);
            setMessage(registerForm, data.message, data.ok);
            if (data.ok) {
                registerForm.reset();
                registerForm.elements.age.value = 8;
            }
        } catch {
            setMessage(registerForm, 'Não consegui conectar agora. Verifique a internet e tente novamente.');
        } finally {
            setSubmitting(registerForm, false);
        }
    });

    const notice = document.querySelector('[data-page-notice]');
    if (notice?.classList.contains('is-visible')) {
        window.setTimeout(() => notice.classList.remove('is-visible'), 7000);
    }
})();
