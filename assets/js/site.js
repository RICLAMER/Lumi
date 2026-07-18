(() => {
    const language = document.body.dataset.language || 'en';
    const copy = {
        en: {
            serverUnreadable: 'The server response could not be read.',
            genericError: 'We could not complete that right now.',
            submitting: 'Just a moment...',
            networkError: 'I could not connect. Check your internet connection and try again.',
            resendEmail: 'Enter the email address to resend the confirmation.',
        },
        pt: {
            serverUnreadable: 'A resposta do servidor não pôde ser lida.',
            genericError: 'Não foi possível concluir agora.',
            submitting: 'Só um instante...',
            networkError: 'Não consegui conectar agora. Verifique a internet e tente novamente.',
            resendEmail: 'Informe o e-mail para reenviar a confirmação.',
        },
        es: {
            serverUnreadable: 'No se pudo leer la respuesta del servidor.',
            genericError: 'No se pudo completar ahora.',
            submitting: 'Un momento...',
            networkError: 'No pude conectarme. Comprueba internet e inténtalo de nuevo.',
            resendEmail: 'Introduce el correo para reenviar la confirmación.',
        },
    }[language] || {};
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

    document.querySelector('[data-language-select]')?.addEventListener('change', (event) => {
        event.currentTarget.form?.submit();
    });

    const postJson = async (url, payload) => {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrf,
                'X-Lumi-Language': language,
            },
            body: JSON.stringify(payload),
        });
        const data = await response.json().catch(() => ({
            ok: false,
            message: copy.serverUnreadable,
        }));
        if (!response.ok && !data.message) data.message = copy.genericError;
        return data;
    };

    const setSubmitting = (form, submitting) => {
        const submit = form.querySelector('[type="submit"]');
        if (!submit) return;
        if (!submit.dataset.label) submit.dataset.label = submit.textContent.trim();
        submit.disabled = submitting;
        submit.textContent = submitting ? copy.submitting : submit.dataset.label;
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
            setMessage(loginForm, copy.networkError);
        } finally {
            setSubmitting(loginForm, false);
        }
    });

    loginForm?.querySelector('[data-resend]')?.addEventListener('click', async (event) => {
        const button = event.currentTarget;
        const email = loginForm.elements.email.value.trim();
        if (!email) {
            setMessage(loginForm, copy.resendEmail);
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
                registerForm.elements.language.value = language;
            }
        } catch {
            setMessage(registerForm, copy.networkError);
        } finally {
            setSubmitting(registerForm, false);
        }
    });

    const notice = document.querySelector('[data-page-notice]');
    if (notice?.classList.contains('is-visible')) {
        window.setTimeout(() => notice.classList.remove('is-visible'), 7000);
    }
})();
