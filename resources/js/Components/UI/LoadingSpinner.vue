<script setup>
defineProps({
    message: {
        type: String,
        default: 'Memproses...'
    },
    size: {
        type: String,
        default: 'md',
        validator: (value) => ['sm', 'md', 'lg'].includes(value)
    }
});

const sizeClasses = {
    sm: 'h-4 w-4',
    md: 'h-8 w-8',
    lg: 'h-12 w-12'
};
</script>

<template>
    <div class="flex flex-col items-center justify-center space-y-3">
        <div class="relative">
            <div :class="['animate-spin rounded-full border-2 border-gray-200', sizeClasses[size]]"></div>
            <div :class="['animate-spin rounded-full border-2 border-t-blue-600 absolute top-0 left-0', sizeClasses[size]]"></div>
        </div>
        <p v-if="message" class="text-sm text-gray-600">{{ message }}</p>
    </div>
</template>