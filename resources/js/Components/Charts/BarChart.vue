<script setup>
import { onMounted, ref, watch } from 'vue';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

const props = defineProps({
    data: {
        type: Object,
        required: true,
    },
    labels: {
        type: Array,
        default: () => ['BALITA', 'ANAK', 'REMAJA', 'PEMUDA', 'DEWASA', 'LANSIA'],
    },
});

const chartRef = ref(null);
let chartInstance = null;

const colors = [
    'rgba(147, 51, 234, 0.8)',   // purple
    'rgba(234, 179, 8, 0.8)',    // yellow
    'rgba(249, 115, 22, 0.8)',   // orange
    'rgba(6, 182, 212, 0.8)',    // cyan
    'rgba(20, 184, 166, 0.8)',   // teal
    'rgba(99, 102, 241, 0.8)',   // indigo
];

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

    const dataValues = props.labels.map(label => props.data[label] || 0);

    chartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: props.labels,
            datasets: [{
                label: 'Jumlah Jamaah',
                data: dataValues,
                backgroundColor: colors,
                borderColor: colors.map(c => c.replace('0.8', '1')),
                borderWidth: 1,
                borderRadius: 8,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false,
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)',
                    },
                },
                x: {
                    grid: {
                        display: false,
                    },
                },
            },
        },
    });
};
</script>

<template>
    <div class="h-64">
        <canvas ref="chartRef"></canvas>
    </div>
</template>
