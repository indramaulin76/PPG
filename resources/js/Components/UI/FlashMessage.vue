<script setup>
import { computed, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();

const flash = computed(() => page.props.flash || {});

const visible = ref(false);
const message = ref('');
const variant = ref('success');

let hideTimer = null;

const show = (text, type) => {
    message.value = text;
    variant.value = type;
    visible.value = true;

    clearTimeout(hideTimer);
    hideTimer = setTimeout(() => {
        visible.value = false;
    }, 5000);
};

watch(
    flash,
    (value) => {
        if (value?.error) {
            show(value.error, 'error');
        } else if (value?.success) {
            show(value.success, 'success');
        }
    },
    { immediate: true, deep: true }
);

const close = () => {
    clearTimeout(hideTimer);
    visible.value = false;
};

const styles = {
    success: 'bg-green-50 border-green-200 text-green-800',
    error: 'bg-red-50 border-red-200 text-red-800',
};
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 -translate-y-2"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-2"
        >
            <div
                v-if="visible"
                class="fixed top-4 left-1/2 z-[60] w-[calc(100%-2rem)] max-w-md -translate-x-1/2"
                role="alert"
                aria-live="polite"
            >
                <div :class="['flex items-start gap-3 rounded-xl border px-4 py-3 shadow-lg', styles[variant]]">
                    <svg v-if="variant === 'success'" class="mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <svg v-else class="mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>

                    <p class="flex-1 text-sm font-medium">{{ message }}</p>

                    <button
                        type="button"
                        class="rounded-lg p-1 opacity-60 transition-opacity hover:opacity-100"
                        aria-label="Tutup notifikasi"
                        @click="close"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
