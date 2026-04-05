<script setup>
defineProps({
    id: String,
    type: {
        type: String,
        default: 'text',
    },
    modelValue: [String, Number],
    placeholder: String,
    required: Boolean,
    disabled: Boolean,
});
defineEmits(['update:modelValue']);
</script>

<template>
    <input
        :id="id"
        :type="type"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        :placeholder="placeholder"
        :required="required"
        :disabled="disabled"
        :class="['border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full py-2', $attrs.class || 'px-3']"
    />
</template>

<script>
export default { inheritAttrs: false }
</script>