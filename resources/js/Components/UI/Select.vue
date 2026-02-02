<script setup>
import { computed } from 'vue';

const props = defineProps({
    modelValue: {
        type: [String, Number],
        default: '',
    },
    label: {
        type: String,
        default: '',
    },
    options: {
        type: Array,
        default: () => [],
    },
    optionValue: {
        type: String,
        default: 'value',
    },
    optionLabel: {
        type: String,
        default: 'label',
    },
    placeholder: {
        type: String,
        default: 'Pilih...',
    },
    error: {
        type: String,
        default: '',
    },
    required: {
        type: Boolean,
        default: false,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:modelValue']);

const selectValue = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

const getOptionValue = (option) => {
    if (!option) return '';
    return typeof option === 'object' ? option[props.optionValue] : option;
};

const getOptionLabel = (option) => {
    if (!option) return '';
    return typeof option === 'object' ? option[props.optionLabel] : option;
};
</script>

<template>
    <div class="space-y-1">
        <label v-if="label" class="block text-sm font-medium text-gray-700">
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>
        <select
            v-model="selectValue"
            :disabled="disabled"
            :class="[
                'block w-full rounded-lg border px-3 py-2 text-sm shadow-sm transition-colors',
                'focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500',
                'disabled:bg-gray-100 disabled:cursor-not-allowed',
                error
                    ? 'border-red-300 text-red-900 focus:ring-red-500 focus:border-red-500'
                    : 'border-gray-300 text-gray-900',
            ]"
        >
            <option value="">{{ placeholder }}</option>
            <option
                v-for="option in options"
                :key="getOptionValue(option)"
                :value="getOptionValue(option)"
            >
                {{ getOptionLabel(option) }}
            </option>
        </select>
        <p v-if="error" class="text-sm text-red-600">{{ error }}</p>
    </div>
</template>
