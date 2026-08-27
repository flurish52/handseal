import { reactive } from 'vue';

const state = reactive({
    open: false,
    title: '',
    message: '',
    confirmLabel: 'Confirm',
    cancelLabel: 'Cancel',
    variant: 'danger', // danger | warning | default
    resolver: null,
});

export function useConfirm() {
    function confirm(options) {
        state.open = true;
        state.title = options.title ?? 'Are you sure?';
        state.message = options.message ?? '';
        state.confirmLabel = options.confirmLabel ?? 'Confirm';
        state.cancelLabel = options.cancelLabel ?? 'Cancel';
        state.variant = options.variant ?? 'danger';

        return new Promise((resolve) => {
            state.resolver = resolve;
        });
    }

    function resolve(value) {
        state.open = false;
        state.resolver?.(value);
        state.resolver = null;
    }

    return { state, confirm, resolve };
}
