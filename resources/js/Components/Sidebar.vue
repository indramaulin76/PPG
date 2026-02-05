<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth?.user || {});

const isActive = (routeName) => {
    return route().current(routeName) || route().current(routeName + '.*');
};

const isSuperAdmin = computed(() => user.value?.role === 'super_admin');
const isAdminDesa = computed(() => user.value?.role === 'admin_desa');
// Admin Kelompok logic is implied by absence of above
</script>

<template>
    <aside class="fixed inset-y-0 left-0 z-50 w-64 bg-white/80 backdrop-blur-xl border-r border-gray-200 shadow-sm hidden lg:flex flex-col">
        <!-- Logo -->
        <div class="flex items-center justify-center h-16 border-b border-gray-100">
            <Link :href="route('dashboard')" class="flex items-center gap-2">
                <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center text-white font-bold">SJ</div>
                <span class="font-bold text-xl text-gray-800 tracking-tight">SIM-J</span>
            </Link>
        </div>

        <!-- Menu -->
        <div class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
            <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 px-3">Main Menu</div>
            
            <Link :href="route('dashboard')" :class="{'bg-blue-50 text-blue-600': isActive('dashboard'), 'text-gray-600 hover:bg-gray-50': !isActive('dashboard')}" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors group">
                <svg class="w-5 h-5 mr-3" :class="isActive('dashboard') ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-500'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
                Dashboard
            </Link>

            <Link :href="route('jamaah.index')" :class="{'bg-blue-50 text-blue-600': isActive('jamaah'), 'text-gray-600 hover:bg-gray-50': !isActive('jamaah')}" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors group">
                <svg class="w-5 h-5 mr-3" :class="isActive('jamaah') ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-500'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                Data Jamaah
            </Link>

            <!-- Wilayah Management (Super Admin only for Desa) -->
            <div v-if="isSuperAdmin || isAdminDesa" class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 mt-6 px-3">Wilayah</div>
            
            <Link v-if="isSuperAdmin" :href="route('wilayah.desa.index')" :class="{'bg-blue-50 text-blue-600': isActive('wilayah.desa'), 'text-gray-600 hover:bg-gray-50': !isActive('wilayah.desa')}" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors group">
                <svg class="w-5 h-5 mr-3" :class="isActive('wilayah.desa') ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-500'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                Data Desa
            </Link>

            <Link v-if="isSuperAdmin || isAdminDesa" :href="route('wilayah.kelompok.index')" :class="{'bg-blue-50 text-blue-600': isActive('wilayah.kelompok'), 'text-gray-600 hover:bg-gray-50': !isActive('wilayah.kelompok')}" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors group">
                <svg class="w-5 h-5 mr-3" :class="isActive('wilayah.kelompok') ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-500'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Data Kelompok
            </Link>

            <!-- Admin Management -->
            <div v-if="isSuperAdmin || isAdminDesa" class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 mt-6 px-3">System</div>
            
            <Link v-if="isSuperAdmin || isAdminDesa" :href="route('admin.index')" :class="{'bg-blue-50 text-blue-600': isActive('admin'), 'text-gray-600 hover:bg-gray-50': !isActive('admin')}" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors group">
                <svg class="w-5 h-5 mr-3" :class="isActive('admin') ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-500'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                Kelola Admin
            </Link>

            <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 mt-6 px-3">Laporan</div>

            <Link :href="route('import.index')" :class="{'bg-blue-50 text-blue-600': isActive('import'), 'text-gray-600 hover:bg-gray-50': !isActive('import')}" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors group">
                 <svg class="w-5 h-5 mr-3" :class="isActive('import') ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-500'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                </svg>
                Import Data
            </Link>

            <a :href="route('export.jamaah')" class="flex items-center px-3 py-2.5 text-sm font-medium text-gray-600 rounded-lg hover:bg-gray-50 transition-colors group">
                <svg class="w-5 h-5 mr-3 text-gray-400 group-hover:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Export CSV
            </a>
        </div>

        <!-- Footer -->
        <div class="p-4 border-t border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white font-bold text-xs uppercase">
                    {{ user.name?.charAt(0) || 'U' }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">{{ user.name || 'User' }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ user.scope_label || user.email || '' }}</p>
                </div>
            </div>
        </div>
    </aside>
</template>
