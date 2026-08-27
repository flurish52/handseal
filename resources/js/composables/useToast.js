import { reactive } from 'vue';

const toasts = reactive([]);
let nextId = 1;

const DURATIONS = { success: 4000, info: 4000, warning: 5000, danger: 6000 };

export function useToast() {
    function push(message, variant = 'info', options = {}) {
        const id = nextId++;
        toasts.push({ id, message, variant });

        const duration = options.duration ?? DURATIONS[variant] ?? 4000;
        if (duration > 0) {
            setTimeout(() => dismiss(id), duration);
        }
        return id;
    }

    function dismiss(id) {
        const index = toasts.findIndex((t) => t.id === id);
        if (index !== -1) toasts.splice(index, 1);
    }

    return {
        toasts,
        success: (msg, opts) => push(msg, 'success', opts),
        danger: (msg, opts) => push(msg, 'danger', opts),
        warning: (msg, opts) => push(msg, 'warning', opts),
        info: (msg, opts) => push(msg, 'info', opts),
        dismiss,
    };
}
