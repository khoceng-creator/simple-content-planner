const themeKey = 'imm-theme';
const toastTimers = new WeakMap();
const selectedContentFiles = new WeakMap();
const contentPreviewUrls = new WeakMap();

function dismissToast(toast) {
    if (!toast || toast.classList.contains('is-leaving')) return;

    const state = toastTimers.get(toast);
    if (state?.timer) window.clearTimeout(state.timer);
    toast.classList.add('is-leaving');
    window.setTimeout(() => {
        const stack = toast.parentElement;
        toast.remove();
        if (stack?.matches('.flash-stack') && !stack.children.length) stack.remove();
    }, 200);
}

function startToastTimer(toast, remaining = Number(toast.dataset.toastDuration) || 5000) {
    const state = {
        remaining,
        startedAt: Date.now(),
        timer: window.setTimeout(() => dismissToast(toast), remaining),
    };
    toastTimers.set(toast, state);
}

function pauseToast(toast) {
    const state = toastTimers.get(toast);
    if (!state?.timer || toast.classList.contains('is-leaving')) return;

    window.clearTimeout(state.timer);
    state.remaining = Math.max(0, state.remaining - (Date.now() - state.startedAt));
    state.timer = null;
    toast.classList.add('is-paused');
}

function resumeToast(toast) {
    const state = toastTimers.get(toast);
    if (!state || state.timer || toast.classList.contains('is-leaving')) return;
    if (toast.matches(':hover') || toast.contains(document.activeElement)) return;

    toast.classList.remove('is-paused');
    startToastTimer(toast, state.remaining);
}

function setupToast(toast) {
    startToastTimer(toast);
    toast.addEventListener('mouseenter', () => pauseToast(toast));
    toast.addEventListener('mouseleave', () => resumeToast(toast));
    toast.addEventListener('focusin', () => pauseToast(toast));
    toast.addEventListener('focusout', (event) => {
        if (!toast.contains(event.relatedTarget)) resumeToast(toast);
    });
}

function applyTheme(theme) {
    const selected = theme === 'light' ? 'light' : 'dark';
    document.documentElement.dataset.theme = selected;
    localStorage.setItem(themeKey, selected);
    document.querySelectorAll('[data-theme]').forEach((button) => {
        button.classList.toggle('active', button.dataset.theme === selected);
    });
}

function openModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.add('open');
    modal.querySelector('input:not([type=hidden]), button, select')?.focus();
}

function closeModal(modal) {
    modal?.classList.remove('open');
}

function resetBrandForm() {
    const form = document.getElementById('brand-form');
    if (!form) return;
    form.reset();
    form.action = form.dataset.createUrl || form.action;
    form.querySelector('[data-method-input]').value = 'POST';
    document.getElementById('brand-modal-title').textContent = 'Tambah brand';
    const preview = form.querySelector('[data-upload-preview]');
    preview.src = '';
    preview.closest('.upload').classList.remove('has-image');
    preview.closest('.upload').classList.remove('is-removing');
    form.querySelector('[data-remove-logo-input]').value = '0';
    const removeButton = form.querySelector('[data-remove-brand-logo]');
    removeButton.hidden = true;
    removeButton.classList.add('danger');
    removeButton.innerHTML = '<span class="icon"><svg><use href="#i-trash"/></svg></span>Hapus logo';
    form.querySelector('[data-brand-logo-help]').textContent = 'JPG, PNG, atau WebP. Maksimal 2 MB.';
    form.dataset.originalLogoUrl = '';
}

function fillBrandForm(payload) {
    const form = document.getElementById('brand-form');
    if (!form) return;
    form.action = payload.update_url;
    form.querySelector('[data-method-input]').value = 'PUT';
    form.querySelector('[name=name]').value = payload.name;
    document.getElementById('brand-modal-title').textContent = 'Edit brand';
    const preview = form.querySelector('[data-upload-preview]');
    if (payload.logo_url) {
        form.dataset.originalLogoUrl = payload.logo_url;
        preview.src = payload.logo_url;
        preview.closest('.upload').classList.add('has-image');
        form.querySelector('[data-remove-brand-logo]').hidden = false;
        form.querySelector('[data-brand-logo-help]').textContent = 'Klik logo untuk memilih pengganti, atau hapus logo saat ini.';
    }
}

function resetContentForm() {
    const form = document.getElementById('content-form');
    if (!form) return;
    const createUrl = form.dataset.createUrl || form.action;
    form.reset();
    form.action = createUrl;
    form.querySelector('[data-method-input]').value = 'POST';
    form.querySelectorAll('[data-editor-for]').forEach((editor) => { editor.innerHTML = ''; });
    form.querySelector('[data-content-type-select]').value = 'carousel';
    toggleNewContentType(form);
    form.querySelector('[name="platforms[instagram]"][type=checkbox]').checked = true;
    form.querySelector('[data-existing-images]').innerHTML = '';
    form.querySelector('[data-existing-media-section]').hidden = true;
    form.querySelector('[data-media-edit-help]').hidden = true;
    selectedContentFiles.set(form, []);
    renderSelectedContentFiles(form);
    updateMediaCount(form);
}

function fillContentForm(payload) {
    const form = document.getElementById('content-form');
    if (!form) return;
    form.action = payload.update_url;
    form.querySelector('[data-method-input]').value = 'PUT';
    ['posting_date', 'posting_time', 'type', 'headline', 'document_link'].forEach((name) => {
        const field = form.querySelector(`[name="${name}"]`);
        if (field) field.value = payload[name] || '';
    });
    toggleNewContentType(form);
    form.querySelector('[name="platforms[instagram]"][type=checkbox]').checked = Boolean(payload.platforms.instagram);
    form.querySelector('[name="platforms[tiktok]"][type=checkbox]').checked = Boolean(payload.platforms.tiktok);
    form.querySelector('[data-editor-for=detail_html]').innerHTML = payload.detail_html || '';
    form.querySelector('[data-editor-for=note_html]').innerHTML = payload.note_html || '';
    form.querySelector('[data-existing-images]').innerHTML = (payload.images || []).map((image) => `
        <div class="existing-image" data-existing-image>
            <img src="${image.url}" alt="${escapeHtml(image.name)}">
            <span class="media-card-name" title="${escapeHtml(image.name)}">${escapeHtml(image.name)}</span>
            <input type="checkbox" name="retain_images[]" value="${image.id}" checked hidden>
            <button class="btn danger media-card-action" type="button" data-remove-existing-image>
                Hapus
            </button>
        </div>
    `).join('');
    form.querySelector('[data-existing-media-section]').hidden = !(payload.images || []).length;
    form.querySelector('[data-media-edit-help]').hidden = false;
    updateMediaCount(form);
}

function fileIdentity(file) {
    return `${file.name}:${file.size}:${file.lastModified}`;
}

function syncContentFileInput(form) {
    const input = form.querySelector('[data-multiple-preview]');
    const transfer = new DataTransfer();
    (selectedContentFiles.get(form) || []).forEach((file) => transfer.items.add(file));
    input.files = transfer.files;
}

function renderSelectedContentFiles(form) {
    (contentPreviewUrls.get(form) || []).forEach((url) => URL.revokeObjectURL(url));

    const files = selectedContentFiles.get(form) || [];
    const urls = files.map((file) => URL.createObjectURL(file));
    contentPreviewUrls.set(form, urls);
    form.querySelector('[data-image-preview-list]').innerHTML = files.map((file, index) => `
        <div class="image-thumb">
            <img src="${urls[index]}" alt="${escapeHtml(file.name)}">
            <span class="media-card-name" title="${escapeHtml(file.name)}">${escapeHtml(file.name)}</span>
            <button class="btn danger media-card-action" type="button" data-remove-new-image="${index}">
                Batalkan
            </button>
        </div>
    `).join('');
    form.querySelector('[data-new-media-section]').hidden = files.length === 0;
    updateMediaCount(form);
}

function updateMediaCount(form) {
    const retained = form.querySelectorAll('[name="retain_images[]"]:checked').length;
    const added = (selectedContentFiles.get(form) || []).length;
    const total = retained + added;
    const counter = form.querySelector('[data-media-count]');
    counter.textContent = `${total} / 12`;
    counter.classList.toggle('is-over', total > 12);
}

function toggleNewContentType(form) {
    const select = form?.querySelector('[data-content-type-select]');
    const field = form?.querySelector('[data-new-content-type]');
    if (!select || !field) return;

    const isNew = select.value === '__new';
    field.hidden = !isNew;
    field.querySelector('input').required = isNew;
    if (isNew) field.querySelector('input').focus();
}

function escapeHtml(value) {
    const element = document.createElement('div');
    element.textContent = value || '';
    return element.innerHTML;
}

document.addEventListener('DOMContentLoaded', () => {
    applyTheme(localStorage.getItem(themeKey) || 'dark');
    document.querySelectorAll('[data-toast]').forEach(setupToast);
    const brandForm = document.getElementById('brand-form');
    const contentForm = document.getElementById('content-form');
    if (brandForm) brandForm.dataset.createUrl = brandForm.action;
    if (contentForm) contentForm.dataset.createUrl = contentForm.action;
    if (contentForm) toggleNewContentType(contentForm);
});

document.addEventListener('click', async (event) => {
    const toastClose = event.target.closest('[data-toast-close]');
    if (toastClose) dismissToast(toastClose.closest('[data-toast]'));

    const themeButton = event.target.closest('[data-theme]');
    if (themeButton) applyTheme(themeButton.dataset.theme);

    const opener = event.target.closest('[data-open-modal]');
    if (opener) {
        if (opener.hasAttribute('data-new-brand')) resetBrandForm();
        if (opener.dataset.brand) {
            resetBrandForm();
            fillBrandForm(JSON.parse(opener.dataset.brand));
        }
        if (opener.hasAttribute('data-new-content')) resetContentForm();
        if (opener.dataset.content) {
            resetContentForm();
            fillContentForm(JSON.parse(opener.dataset.content));
        }
        openModal(opener.dataset.openModal);
    }

    const removeBrandLogo = event.target.closest('[data-remove-brand-logo]');
    if (removeBrandLogo) {
        const form = removeBrandLogo.closest('form');
        const preview = form.querySelector('[data-upload-preview]');
        const isRemoving = form.querySelector('[data-remove-logo-input]').value === '1';
        const willRemove = !isRemoving;
        const originalLogoUrl = form.dataset.originalLogoUrl || '';
        if (willRemove) {
            form.querySelector('[name="logo"]').value = '';
            preview.src = originalLogoUrl;
            preview.closest('.upload').classList.toggle('has-image', Boolean(originalLogoUrl));
        }
        if (!originalLogoUrl && willRemove) {
            form.querySelector('[data-remove-logo-input]').value = '0';
            removeBrandLogo.hidden = true;
            form.querySelector('[data-brand-logo-help]').textContent = 'JPG, PNG, atau WebP. Maksimal 2 MB.';
            return;
        }
        form.querySelector('[data-remove-logo-input]').value = isRemoving ? '0' : '1';
        removeBrandLogo.classList.toggle('danger', isRemoving);
        removeBrandLogo.innerHTML = isRemoving
            ? '<span class="icon"><svg><use href="#i-trash"/></svg></span>Hapus logo'
            : 'Batalkan hapus';
        preview.closest('.upload').classList.toggle('is-removing', willRemove);
        form.querySelector('[data-brand-logo-help]').textContent = isRemoving
            ? 'Klik logo untuk memilih pengganti, atau hapus logo saat ini.'
            : 'Logo akan dihapus setelah perubahan disimpan.';
    }

    const removeExistingImage = event.target.closest('[data-remove-existing-image]');
    if (removeExistingImage) {
        const card = removeExistingImage.closest('[data-existing-image]');
        const checkbox = card.querySelector('[name="retain_images[]"]');
        checkbox.checked = !checkbox.checked;
        card.classList.toggle('is-removed', !checkbox.checked);
        removeExistingImage.classList.toggle('danger', checkbox.checked);
        removeExistingImage.textContent = checkbox.checked ? 'Hapus' : 'Batalkan hapus';
        updateMediaCount(removeExistingImage.closest('form'));
    }

    const removeNewImage = event.target.closest('[data-remove-new-image]');
    if (removeNewImage) {
        const form = removeNewImage.closest('form');
        const files = selectedContentFiles.get(form) || [];
        files.splice(Number(removeNewImage.dataset.removeNewImage), 1);
        selectedContentFiles.set(form, files);
        syncContentFileInput(form);
        renderSelectedContentFiles(form);
    }

    const closer = event.target.closest('[data-close-modal]');
    if (closer) closeModal(closer.closest('[data-modal]'));
    if (event.target.matches('[data-modal]')) closeModal(event.target);

    const editorTool = event.target.closest('[data-editor-command]');
    if (editorTool) {
        event.preventDefault();
        const editor = editorTool.closest('.rich-editor').querySelector('[contenteditable]');
        editor.focus();
        let argument = null;
        if (editorTool.dataset.editorCommand === 'createLink') {
            argument = window.prompt('Masukkan URL http/https:');
            if (!argument) return;
        }
        document.execCommand(editorTool.dataset.editorCommand, false, argument);
    }

    const copyButton = event.target.closest('[data-copy]');
    if (copyButton) {
        await navigator.clipboard?.writeText(copyButton.dataset.copy);
        copyButton.textContent = 'Tersalin';
    }

    const shareButton = event.target.closest('[data-share-summary]');
    if (shareButton) {
        const text = shareButton.dataset.shareSummary.trim();
        if (navigator.share) {
            try {
                await navigator.share({ title: document.title, text });
                return;
            } catch (_) {}
        }
        await navigator.clipboard?.writeText(text);
        shareButton.textContent = 'Ringkasan tersalin';
    }
});

document.addEventListener('change', (event) => {
    if (event.target.matches('[data-content-type-select]')) {
        toggleNewContentType(event.target.closest('form'));
    }

    if (event.target.matches('[data-preview-input]')) {
        const file = event.target.files?.[0];
        if (!file) return;
        const form = event.target.closest('form');
        const preview = form.querySelector('[data-upload-preview]');
        preview.src = URL.createObjectURL(file);
        preview.closest('.upload').classList.add('has-image');
        preview.closest('.upload').classList.remove('is-removing');
        form.querySelector('[data-remove-logo-input]').value = '0';
        form.querySelector('[data-remove-brand-logo]').hidden = false;
        form.querySelector('[data-remove-brand-logo]').innerHTML = '<span class="icon"><svg><use href="#i-trash"/></svg></span>Hapus logo';
        form.querySelector('[data-remove-brand-logo]').classList.add('danger');
        form.querySelector('[data-brand-logo-help]').textContent = 'Logo baru dipilih dan akan menggantikan logo lama saat disimpan.';
    }

    if (event.target.matches('[data-multiple-preview]')) {
        const form = event.target.closest('form');
        const current = selectedContentFiles.get(form) || [];
        const identities = new Set(current.map(fileIdentity));
        const additions = Array.from(event.target.files || []).filter((file) => !identities.has(fileIdentity(file)));
        selectedContentFiles.set(form, [...current, ...additions]);
        syncContentFileInput(form);
        renderSelectedContentFiles(form);
    }
});

document.addEventListener('submit', (event) => {
    const form = event.target;
    if (form.dataset.confirm && !window.confirm(form.dataset.confirm)) {
        event.preventDefault();
        return;
    }
    form.querySelectorAll('[data-editor-for]').forEach((editor) => {
        form.querySelector(`[name="${editor.dataset.editorFor}"]`).value = editor.innerHTML.trim();
    });
});
