<script setup>
defineProps({
    title: {
        type: String,
        default: '',
    },
    padding: {
        type: Boolean,
        default: true,
    },
});
</script>

<template>
    <div class="rounded-2xl bg-white shadow-sm overflow-hidden">
        <div v-if="title || $slots.header" class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
            <slot name="header">
                <h3 class="text-lg font-semibold text-gray-900">{{ title }}</h3>
            </slot>
            <slot name="actions" />
        </div>
        <div :class="padding ? 'p-6' : ''">
            <slot />
        </div>
        <div v-if="$slots.footer" class="border-t border-gray-100 px-6 py-4 bg-gray-50">
            <slot name="footer" />
        </div>
    </div>
</template>
