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
    video: 'video/mp4,video/webm,video/quicktime,video/x-msvideo',
    audio: 'audio/mpeg,audio/wav,audio/mp4,audio/ogg',
    file: '.pdf,.jpg,.jpeg,.png,.webp,.zip,.doc,.docx',
};

Alpine.data('lessonBuilder', () => ({
    state: 'idle',
    title: '',
    upload: null,
    submitting: false,
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
            error: 'تعذّر الحفظ',
        }[this.state] ?? '';
    },

    uploadLabel() {
        if (!this.upload) {
            return '';
        }

        return {
            uploading: `جارٍ الرفع ${this.upload.percent}%`,
            processing: 'جارٍ المعالجة…',
            error: 'فشل الرفع، حاول مرة أخرى.',
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
        body.append('type', this.$refs.type.value);
        body.append('file', file);

        this.upload = { name: file.name, percent: 0, state: 'uploading' };
        this.$refs.file.value = '';

        const request = new XMLHttpRequest();
        request.open('POST', this.$refs.addForm.action);
        request.setRequestHeader('X-CSRF-TOKEN', this.csrf);
        request.setRequestHeader('Accept', 'application/json');

        request.upload.addEventListener('progress', (event) => {
            if (event.lengthComputable) {
                this.upload = { ...this.upload, percent: Math.round((event.loaded / event.total) * 100) };
            }
        });

        request.addEventListener('load', () => {
            if (request.status >= 400) {
                this.upload = { ...this.upload, state: 'error' };

                return;
            }

            this.upload = { ...this.upload, state: 'processing' };
            this.submitting = true;
            window.location.reload();
        });

        request.addEventListener('error', () => {
            this.upload = { ...this.upload, state: 'error' };
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
                throw new Error('Could not save the content block.');
            }

            this.state = 'saved';
        } catch (error) {
            this.state = 'error';
        }
    },
}));

Alpine.start();
