<script setup>
defineProps({
    status: {
        type: String,
        required: true,
    },
});

const colors = {
    MENIKAH: 'bg-green-100 text-green-800',
    BELUM: 'bg-gray-100 text-gray-800',
    JANDA: 'bg-red-100 text-red-800',
    DUDA: 'bg-red-100 text-red-800',
    L: 'bg-blue-100 text-blue-800',
    P: 'bg-pink-100 text-pink-800',
    BALITA: 'bg-purple-100 text-purple-800',
    ANAK: 'bg-yellow-100 text-yellow-800',
    REMAJA: 'bg-orange-100 text-orange-800',
    PEMUDA: 'bg-cyan-100 text-cyan-800',
    DEWASA: 'bg-teal-100 text-teal-800',
    LANSIA: 'bg-indigo-100 text-indigo-800',
};

const labels = {
    L: 'Laki-laki',
    P: 'Perempuan',
};
</script>

<template>
    <span
        :class="[
            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
            colors[status] || 'bg-gray-100 text-gray-800'
        ]"
    >
        {{ labels[status] || status }}
    </span>
</template>
