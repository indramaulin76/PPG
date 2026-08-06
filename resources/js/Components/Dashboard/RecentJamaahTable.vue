<script setup>
import { Link } from '@inertiajs/vue3';
import DataBadge from '@/Components/Data/DataBadge.vue';

defineProps({
    title: {
        type: String,
        default: 'Jamaah Terbaru',
    },
    items: {
        type: Array,
        default: () => [],
    },
    // 'wilayah' shows Desa + Kelompok, 'kelompok' shows Kelompok only, 'none' hides the column.
    scopeColumn: {
        type: String,
        default: 'none',
    },
});
</script>

<template>
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-50 flex items-center justify-between bg-white">
            <h3 class="text-lg font-bold text-gray-900 tracking-tight">{{ title }}</h3>
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
                        <th v-if="scopeColumn !== 'none'" class="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em]">
                            {{ scopeColumn === 'wilayah' ? 'Wilayah' : 'Kelompok' }}
                        </th>
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em]">Umur</th>
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em]">Status</th>
                        <th class="px-6 py-4 text-right text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="person in items" :key="person.id" class="group hover:bg-blue-50/30 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-9 w-9 rounded-xl bg-gradient-to-br from-gray-100 to-gray-200 text-gray-600 flex items-center justify-center font-bold text-sm mr-3 group-hover:from-blue-500 group-hover:to-indigo-600 group-hover:text-white transition-all duration-300">
                                    {{ person.name.charAt(0) }}
                                </div>
                                <div class="text-sm font-bold text-gray-900">{{ person.name }}</div>
                            </div>
                        </td>
                        <td v-if="scopeColumn === 'wilayah'" class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-gray-800">{{ person.desa }}</div>
                            <div class="text-xs text-gray-400">{{ person.kelompok }}</div>
                        </td>
                        <td v-else-if="scopeColumn === 'kelompok'" class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ person.kelompok }}</div>
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
            <div v-for="person in items" :key="person.id" class="p-4 active:bg-gray-50 transition-colors">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                            {{ person.name.charAt(0) }}
                        </div>
                        <div>
                            <div class="text-sm font-bold text-gray-900">{{ person.name }}</div>
                            <div v-if="scopeColumn === 'wilayah'" class="text-[10px] text-gray-500 font-medium">{{ person.desa }} • {{ person.kelompok }}</div>
                            <div v-else-if="scopeColumn === 'kelompok'" class="text-[10px] text-gray-500 font-medium">{{ person.kelompok }}</div>
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

            <div v-if="items.length === 0" class="px-6 py-12 text-center text-gray-500">
                <p class="text-sm font-medium">Belum ada data jamaah.</p>
            </div>
        </div>

        <div v-if="items.length === 0" class="hidden md:block px-6 py-12 text-center text-gray-500">
            <p class="text-sm font-medium">Belum ada data jamaah.</p>
        </div>
    </div>
</template>
