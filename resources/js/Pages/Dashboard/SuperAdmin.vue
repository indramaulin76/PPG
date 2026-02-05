<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import BarChart from '@/Components/Charts/BarChart.vue';
import PieChart from '@/Components/Charts/PieChart.vue';
import DataBadge from '@/Components/Data/DataBadge.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            totalJamaah: 0,
            totalKK: 0,
            totalDesa: 0,
            totalKelompok: 0,
            genderRatio: '50:50',
        }),
    },
    ageDistribution: Object,
    maritalDistribution: Object,
    recentJamaah: Array,
});

const statCards = [
    { name: 'Total Jamaah', key: 'totalJamaah', icon: 'users', color: 'from-blue-500 to-blue-600' },
    { name: 'Total Desa', key: 'totalDesa', icon: 'map', color: 'from-orange-500 to-orange-600' },
    { name: 'Total Kelompok', key: 'totalKelompok', icon: 'collection', color: 'from-indigo-500 to-indigo-600' },
    { name: 'Rasio L/P', key: 'genderRatio', icon: 'scale', color: 'from-purple-500 to-purple-600' },
];

const formatNumber = (num) => {
    return new Intl.NumberFormat('id-ID').format(num);
};
</script>

<template>
    <AppLayout title="Dashboard Super Admin">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Super Admin Dashboard
                </h2>
                <div class="flex space-x-3">
                    <Link :href="route('admin.index')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                        Kelola Admin
                    </Link>
                    <Link :href="route('wilayah.desa.index')" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                        Kelola Wilayah
                    </Link>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <div v-for="stat in statCards" :key="stat.key" class="relative overflow-hidden rounded-2xl bg-white p-6 shadow-sm hover:shadow-md transition-shadow">
                    <dt>
                        <div class="absolute rounded-md p-3 bg-gradient-to-br" :class="stat.color">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path v-if="stat.icon === 'users'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                <path v-else-if="stat.icon === 'map'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                <path v-else-if="stat.icon === 'collection'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                <path v-else-if="stat.icon === 'scale'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2" />
                            </svg>
                        </div>
                        <p class="ml-16 truncate text-sm font-medium text-gray-500">{{ stat.name }}</p>
                    </dt>
                    <dd class="ml-16 flex items-baseline pb-1 sm:pb-2">
                        <p class="text-2xl font-semibold text-gray-900">
                            {{ stat.key === 'genderRatio' ? stats[stat.key] : formatNumber(stats[stat.key]) }}
                        </p>
                    </dd>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Chart 1: Usia -->
                <div class="bg-white rounded-2xl shadow-sm p-6 lg:col-span-2">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Sebaran Usia Global</h3>
                    <BarChart :data="ageDistribution" />
                </div>

                <!-- Chart 2: Status -->
                <div class="bg-white rounded-2xl shadow-sm p-6">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Status Pernikahan</h3>
                    <PieChart :data="maritalDistribution" />
                </div>
            </div>

            <!-- Recent Data Table -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">Jamaah Terbaru (System-Wide)</h3>
                    <Link :href="route('jamaah.index')" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                        Lihat Semua
                    </Link>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50/50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Lengkap</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Desa - Kelompok</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Umur</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="person in recentJamaah" :key="person.id" class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="h-8 w-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xs mr-3">
                                            {{ person.name.charAt(0) }}
                                        </div>
                                        <div class="text-sm font-medium text-gray-900">{{ person.name }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ person.desa }}</div>
                                    <div class="text-xs text-gray-500">{{ person.kelompok }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ person.umur || '-' }} th
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <DataBadge :status="person.status" />
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <Link :href="route('jamaah.edit', person.id)" class="text-blue-600 hover:text-blue-900">Edit</Link>
                                </td>
                            </tr>
                            <tr v-if="recentJamaah.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                    Belum ada data jamaah.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
