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
        :type="type"
        :disabled="disabled"
        class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-wider hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-25"
        @click="$emit('click')"
    >
        <slot />
    </button>
</template>