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
    { name: 'Total Jamaah', key: 'totalJamaah', icon: 'users', color: 'bg-blue-600', shadow: 'shadow-blue-100' },
    { name: 'Total Desa', key: 'totalDesa', icon: 'map', color: 'bg-orange-500', shadow: 'shadow-orange-100' },
    { name: 'Total Kelompok', key: 'totalKelompok', icon: 'collection', color: 'bg-indigo-600', shadow: 'shadow-indigo-100' },
    { name: 'Rasio L/P', key: 'genderRatio', icon: 'scale', color: 'bg-purple-600', shadow: 'shadow-purple-100' },
];

const formatNumber = (num) => {
    return new Intl.NumberFormat('id-ID').format(num);
};
</script>

<template>
    <AppLayout title="Dashboard Super Admin">
        <template #header>
            Dashboard
        </template>

        <div class="space-y-6 sm:space-y-8">
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 gap-4 sm:gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div v-for="stat in statCards" :key="stat.key" class="group relative overflow-hidden rounded-2xl bg-white p-5 sm:p-6 shadow-sm border border-gray-100 hover:shadow-md transition-all duration-300">
                    <div class="flex items-center gap-4">
                        <div class="rounded-xl p-3 text-white shadow-lg shrink-0" :class="[stat.color, stat.shadow]">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path v-if="stat.icon === 'users'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                <path v-else-if="stat.icon === 'map'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                <path v-else-if="stat.icon === 'collection'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                <path v-else-if="stat.icon === 'scale'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-0.5 truncate">{{ stat.name }}</p>
                            <p class="text-2xl font-black text-gray-900 leading-none">
                                {{ stat.key === 'genderRatio' ? stats[stat.key] : formatNumber(stats[stat.key]) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 lg:col-span-2">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-bold text-gray-900">Sebaran Usia Global</h3>
                        <div class="flex gap-1">
                            <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                            <div class="w-2 h-2 rounded-full bg-blue-300"></div>
                        </div>
                    </div>
                    <div class="h-[300px]">
                        <BarChart :data="ageDistribution" />
                    </div>
                </div>

                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-6">Status Pernikahan</h3>
                    <div class="h-[300px] flex items-center justify-center">
                        <PieChart :data="maritalDistribution" />
                    </div>
                </div>
            </div>

            <!-- Recent Data Section -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-50 flex items-center justify-between bg-white">
                    <h3 class="text-lg font-bold text-gray-900 tracking-tight">Jamaah Terbaru</h3>
                    <Link :href="route('jamaah.index')" class="text-xs font-bold text-blue-600 hover:text-blue-800 uppercase tracking-widest transition-colors bg-blue-50 px-3 py-1.5 rounded-lg">
                        Lihat Semua
                    </Link>
                </div>

                <!-- Desktop Table (Visible on md and up) -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead>
                            <tr class="bg-gray-50/50">
                                <th class="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em]">Nama Lengkap</th>
                                <th class="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em]">Wilayah</th>
                                <th class="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em]">Umur</th>
                                <th class="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em]">Status</th>
                                <th class="px-6 py-4 text-right text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em]">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="person in recentJamaah" :key="person.id" class="group hover:bg-blue-50/30 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="h-9 w-9 rounded-xl bg-gradient-to-br from-gray-100 to-gray-200 text-gray-600 flex items-center justify-center font-bold text-sm mr-3 group-hover:from-blue-500 group-hover:to-indigo-600 group-hover:text-white transition-all duration-300">
                                            {{ person.name.charAt(0) }}
                                        </div>
                                        <div class="text-sm font-bold text-gray-900">{{ person.name }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-gray-800">{{ person.desa }}</div>
                                    <div class="text-xs text-gray-400">{{ person.kelompok }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-600">
                                    {{ person.umur || '-' }} th
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <DataBadge :status="person.status" />
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link :href="route('jamaah.show', person.id)" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-indigo-600 hover:bg-indigo-600 hover:text-white transition-all duration-200" title="Detail">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                        </Link>
                                        <Link :href="route('jamaah.edit', person.id)" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-blue-600 hover:bg-blue-600 hover:text-white transition-all duration-200" title="Edit">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile List (Visible on mobile only) -->
                <div class="md:hidden divide-y divide-gray-50">
                    <div v-for="person in recentJamaah" :key="person.id" class="p-4 active:bg-gray-50 transition-colors">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                                    {{ person.name.charAt(0) }}
                                </div>
                                <div>
                                    <div class="text-sm font-bold text-gray-900">{{ person.name }}</div>
                                    <div class="text-[10px] text-gray-500 font-medium">{{ person.desa }} • {{ person.kelompok }}</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-1">
                                <Link :href="route('jamaah.show', person.id)" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                </Link>
                                <Link :href="route('jamaah.edit', person.id)" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </Link>
                            </div>
                        </div>
                        <div class="mt-3 flex items-center justify-between">
                            <div class="text-xs font-bold text-gray-400 uppercase tracking-tighter">{{ person.umur || '-' }} Tahun</div>
                            <DataBadge :status="person.status" />
                        </div>
                    </div>
                </div>

                <div v-if="recentJamaah.length === 0" class="px-6 py-12 text-center text-gray-500">
                    <p class="text-sm font-medium">Belum ada data jamaah.</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
