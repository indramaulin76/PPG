<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
    disabled: Boolean,
    class: {
        type: String,
        default: '',
    },
});
defineEmits(['click']);
</script>

<template>
    <button
        type="button"
        class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-wider hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-25"
        :disabled="disabled"
        @click="$emit('click', $event)"
    >
        <slot />
    </button>
</template>