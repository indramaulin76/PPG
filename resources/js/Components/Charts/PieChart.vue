<script setup>
import { onMounted, ref, watch } from 'vue';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

const props = defineProps({
    data: {
        type: Object,
        required: true,
    },
});

const chartRef = ref(null);
let chartInstance = null;

const labelMap = {
    'BELUM': 'Belum Menikah',
    'MENIKAH': 'Menikah',
    'JANDA': 'Janda',
    'DUDA': 'Duda',
};

const colors = {
    'BELUM': 'rgba(156, 163, 175, 0.8)',
    'MENIKAH': 'rgba(34, 197, 94, 0.8)',
    'JANDA': 'rgba(239, 68, 68, 0.8)',
    'DUDA': 'rgba(249, 115, 22, 0.8)',
};

onMounted(() => {
    createChart();
});

watch(() => props.data, () => {
    if (chartInstance) {
        chartInstance.destroy();
    }
    createChart();
}, { deep: true });

const createChart = () => {
    const ctx = chartRef.value?.getContext('2d');
    if (!ctx) return;

    const labels = Object.keys(props.data).map(k => labelMap[k] || k);
    const dataValues = Object.values(props.data);
    const bgColors = Object.keys(props.data).map(k => colors[k] || 'rgba(99, 102, 241, 0.8)');

    chartInstance = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: dataValues,
                backgroundColor: bgColors,
                borderWidth: 0,
                hoverOffset: 4,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        usePointStyle: true,
                    },
                },
            },
            cutout: '60%',
        },
    });
};
</script>

<template>
    <div class="relative w-full h-64 overflow-hidden">
        <canvas ref="chartRef"></canvas>
    </div>
</template>
