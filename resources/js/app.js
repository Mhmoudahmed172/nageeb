import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';
import Sortable from 'sortablejs';

document.documentElement.setAttribute('dir', 'rtl');
document.documentElement.setAttribute('lang', 'ar');

window.Chart = Chart;
window.Sortable = Sortable;
window.Alpine = Alpine;

const sendOrder = (list, csrf) => fetch(list.dataset.reorderUrl, {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrf,
    },
    body: JSON.stringify({
        ids: [...list.children]
            .filter((item) => item.dataset.id)
            .map((item) => Number(item.dataset.id)),
    }),
});

const bindSortableLists = (root, csrf, { onStart, onDone }) => {
    root.querySelectorAll('[data-sortable]').forEach((list) => {
        Sortable.create(list, {
            animation: 160,
            handle: '.drag-handle',
            draggable: ':scope > [data-id]',
            ghostClass: 'curriculum-sort-ghost',
            chosenClass: 'curriculum-sort-chosen',
            onEnd: async () => {
                onStart();

                try {
                    const response = await sendOrder(list, csrf);

                    if (!response.ok) {
                        throw new Error('Could not save order.');
                    }

                    onDone(true);
                } catch (error) {
                    window.location.reload();
                }
            },
        });
    });
};

Alpine.data('curriculumWorkspace', () => ({
    saving: false,
    init() {
        bindSortableLists(this.$root, this.$root.dataset.csrf, {
            onStart: () => { this.saving = true; },
            onDone: () => { this.saving = false; },
        });
    },
}));

const MEDIA_TYPES = {
    video: 'video/mp4,video/webm,video/quicktime,video/x-msvideo,video/x-m4v',
    audio: 'audio/mpeg,audio/wav,audio/mp4,audio/ogg',
    file: '.pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.jpg,.jpeg,.png,.webp,.zip',
};

const uploadErrorMessage = (request) => {
    if (request.status === 413) {
        return 'حجم الملف أكبر من الحد المسموح.';
    }

    if (request.status === 419) {
        return 'انتهت جلسة الرفع. حدّث الصفحة وحاول مرة أخرى.';
    }

    if (request.status === 403) {
        return 'ليس لديك صلاحية رفع هذا الملف.';
    }

    if (request.status === 401) {
        return 'انتهت جلسة الرفع. سجّل الدخول ثم حاول مرة أخرى.';
    }

    try {
        const payload = JSON.parse(request.responseText || '{}');

        if (payload.message) {
            return payload.message;
        }

        const first = payload.errors && Object.values(payload.errors).flat()[0];

        if (first) {
            return first;
        }
    } catch (error) {
        // Fall through to the status-based message.
    }

    if (request.status === 422) {
        return 'البيانات المرفوعة غير صالحة.';
    }

    return 'فشل الرفع، حاول مرة أخرى.';
};

Alpine.data('lessonBuilder', () => ({
    state: 'idle',
    title: '',
    upload: null,
    submitting: false,
    errorMessage: '',
    timers: {},

    init() {
        this.title = this.$root.querySelector('#lesson-settings [name="title"]')?.value ?? '';

        bindSortableLists(this.$root, this.csrf, {
            onStart: () => { this.state = 'saving'; },
            onDone: () => { this.state = 'saved'; },
        });

        window.addEventListener('beforeunload', (event) => {
            if (this.submitting || !['dirty', 'saving'].includes(this.state)) {
                return;
            }

            event.preventDefault();
            event.returnValue = '';
        });
    },

    get csrf() {
        return this.$root.dataset.csrf;
    },

    stateLabel() {
        return {
            saving: 'جارٍ الحفظ…',
            saved: 'تم الحفظ',
            dirty: 'تغييرات غير محفوظة',
            error: this.errorMessage || 'تعذّر الحفظ',
        }[this.state] ?? '';
    },

    uploadLabel() {
        if (!this.upload) {
            return '';
        }

        return {
            uploading: `جارٍ الرفع ${this.upload.percent}%`,
            processing: 'جارٍ المعالجة…',
            error: this.upload.message || 'فشل الرفع، حاول مرة أخرى.',
        }[this.upload.state];
    },

    markDirty() {
        this.state = 'dirty';
    },

    pickType(type) {
        this.$refs.type.value = type;

        if (MEDIA_TYPES[type]) {
            this.$refs.file.accept = MEDIA_TYPES[type];
            this.$refs.file.click();

            return;
        }

        this.$refs.addForm.submit();
    },

    uploadSelected() {
        const file = this.$refs.file.files[0];

        if (!file) {
            return;
        }

        const body = new FormData();
        body.append('_token', this.csrf);
        body.append('type', this.$refs.type.value);
        body.append('file', file);

        this.upload = { name: file.name, percent: 0, state: 'uploading', message: '' };
        this.$refs.file.value = '';

        const request = new XMLHttpRequest();
        request.open('POST', this.$refs.addForm.action);
        request.setRequestHeader('X-CSRF-TOKEN', this.csrf);
        request.setRequestHeader('Accept', 'application/json');
        request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

        request.upload.addEventListener('progress', (event) => {
            if (event.lengthComputable) {
                this.upload = { ...this.upload, percent: Math.round((event.loaded / event.total) * 100) };
            }
        });

        request.addEventListener('load', () => {
            if (request.status >= 400) {
                this.upload = {
                    ...this.upload,
                    state: 'error',
                    message: uploadErrorMessage(request),
                };

                return;
            }

            this.upload = { ...this.upload, state: 'processing', percent: 100 };
            this.submitting = true;
            window.location.reload();
        });

        request.addEventListener('error', () => {
            this.upload = { ...this.upload, state: 'error', message: 'تعذّر الاتصال بالخادم أثناء الرفع.' };
        });

        request.send(body);
    },

    scheduleSave(event) {
        const block = event.target.closest('[data-block]');

        if (!block) {
            return;
        }

        this.markDirty();
        clearTimeout(this.timers[block.dataset.id]);
        this.timers[block.dataset.id] = setTimeout(() => this.saveBlock(block), 700);
    },

    async saveBlock(block) {
        this.state = 'saving';

        const body = new FormData();
        body.append('_method', 'PUT');

        block.querySelectorAll('[name]').forEach((input) => {
            if (input.closest('form')) {
                return;
            }

            if (['checkbox', 'radio'].includes(input.type) && !input.checked) {
                return;
            }

            body.append(input.name, input.value);
        });

        try {
            const response = await fetch(block.dataset.saveUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrf,
                },
                body,
            });

            if (!response.ok) {
                let message = 'تعذّر الحفظ';

                try {
                    const payload = await response.json();
                    message = payload.message
                        || (payload.errors && Object.values(payload.errors).flat()[0])
                        || message;
                } catch (parseError) {
                    // Keep the fallback label.
                }

                this.state = 'error';
                this.errorMessage = message;
                throw new Error(message);
            }

            this.state = 'saved';
        } catch (error) {
            this.state = 'error';
        }
    },
}));

document.documentElement.classList.add('js-ready');

const prefersReducedMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;

Alpine.data('siteNav', () => ({
    scrolled: false,
    open: false,
    init() {
        const onScroll = () => {
            this.scrolled = window.scrollY > 10;
        };

        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
    },
}));

Alpine.data('reveal', (delay = 0) => ({
    visible: false,
    init() {
        if (prefersReducedMotion()) {
            this.visible = true;
            return;
        }

        this.$el.style.transitionDelay = `${delay}ms`;

        const observer = new IntersectionObserver(([entry]) => {
            if (! entry.isIntersecting) {
                return;
            }

            this.visible = true;
            observer.disconnect();
        }, { threshold: 0.14, rootMargin: '0px 0px -6% 0px' });

        observer.observe(this.$el);
    },
}));

Alpine.data('countUp', (target) => ({
    display: String(target),
    init() {
        const raw = String(target);
        const prefix = (raw.match(/^[^\d]+/) || [''])[0];
        const remainder = raw.slice(prefix.length);
        const end = Number(remainder.replace(/[^\d.-]/g, '')) || 0;
        const suffix = remainder.replace(/^[\d.,\s-]+/, '');

        if (prefersReducedMotion() || end <= 0) {
            this.display = raw;
            return;
        }

        const observer = new IntersectionObserver(([entry]) => {
            if (! entry.isIntersecting) {
                return;
            }

            const started = performance.now();
            const duration = 900;
            const step = (now) => {
                const progress = Math.min((now - started) / duration, 1);
                const eased = 1 - (1 - progress) ** 3;
                this.display = `${prefix}${Math.round(end * eased).toLocaleString('en-US')}${suffix}`;

                if (progress < 1) {
                    requestAnimationFrame(step);
                }
            };

            requestAnimationFrame(step);
            observer.disconnect();
        }, { threshold: 0.4 });

        observer.observe(this.$el);
    },
}));

Alpine.start();
