<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

defineProps({
    isOpen: {
        type: Boolean,
        default: false,
    },
});

defineEmits(['close']);

const page = usePage();
const user = computed(() => page.props.auth?.user || {});

const isActive = (routeName) => {
    return route().current(routeName) || route().current(routeName + '.*');
};

const isSuperAdmin = computed(() => user.value?.role === 'super_admin' || user.value?.role === 'developer');
const isAdminDesa = computed(() => user.value?.role === 'admin_desa');
</script>

<template>
    <!-- Mobile Backdrop & Sidebar -->
    <div class="relative z-50 lg:h-full lg:flex-shrink-0">
        <!-- Mobile Overlay -->
        <Transition
            enter-active-class="transition-opacity ease-linear duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity ease-linear duration-300"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="isOpen" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm lg:hidden" @click="$emit('close')"></div>
        </Transition>

        <!-- Sidebar Container -->
        <aside 
            class="fixed inset-y-0 left-0 w-64 bg-white border-r border-gray-200 shadow-2xl transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0 lg:shadow-none lg:h-full z-50 flex flex-col"
            :class="isOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <!-- Logo Area -->
            <div class="flex items-center justify-between min-h-[5rem] py-3 px-6 border-b border-gray-100 flex-shrink-0">
                <Link :href="route('dashboard')" class="flex items-center gap-3" @click="$emit('close')">
                    <div class="w-12 h-12 flex items-center justify-center shrink-0">
                        <img v-if="$page.props.global_settings.app_logo" :src="$page.props.global_settings.app_logo" class="w-full h-full object-contain rounded-xl shadow-sm" alt="Logo" />
                        <div v-else class="w-full h-full bg-gradient-to-br from-blue-600 to-indigo-700 rounded-xl flex items-center justify-center text-white shadow-lg shadow-blue-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <span class="font-bold text-gray-900 text-sm sm:text-base leading-tight block break-words" :title="$page.props.global_settings.app_name">
                            {{ $page.props.global_settings.app_name }}
                        </span>
                    </div>
                </Link>
                
                <button 
                    class="lg:hidden p-2 -mr-2 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-50 transition-colors"
                    @click="$emit('close')"
                >
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Menu Content -->
            <nav class="flex-1 overflow-y-auto py-4 px-4 space-y-5 scrollbar-hide">
                <!-- Group 1 -->
                <div>
                    <div class="px-3 mb-2 text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em]">Dashboard & Data</div>
                    <div class="space-y-1">
                        <Link :href="route('dashboard')" :class="isActive('dashboard') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'" class="flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl transition-all duration-200 group" @click="$emit('close')">
                            <svg class="w-5 h-5 mr-3 transition-colors" :class="isActive('dashboard') ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-500'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                            Dashboard
                        </Link>

                        <Link :href="route('jamaah.index')" :class="isActive('jamaah') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'" class="flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl transition-all duration-200 group" @click="$emit('close')">
                            <svg class="w-5 h-5 mr-3 transition-colors" :class="isActive('jamaah') ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-500'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            Data Jamaah
                        </Link>
                    </div>
                </div>

                <!-- Group 2: Wilayah -->
                <div v-if="isSuperAdmin || isAdminDesa">
                    <div class="px-3 mb-2 text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em]">Wilayah</div>
                    <div class="space-y-1">
                        <Link v-if="isSuperAdmin" :href="route('wilayah.desa.index')" :class="isActive('wilayah.desa') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'" class="flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl transition-all duration-200 group" @click="$emit('close')">
                            <svg class="w-5 h-5 mr-3 transition-colors" :class="isActive('wilayah.desa') ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-500'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            Data Desa
                        </Link>

                        <Link v-if="isSuperAdmin || isAdminDesa" :href="route('wilayah.kelompok.index')" :class="isActive('wilayah.kelompok') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'" class="flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl transition-all duration-200 group" @click="$emit('close')">
                            <svg class="w-5 h-5 mr-3 transition-colors" :class="isActive('wilayah.kelompok') ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-500'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Data Kelompok
                        </Link>
                    </div>
                </div>

                <!-- Group 3: System -->
                <div>
                    <div class="px-3 mb-2 text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em]">Sistem & Laporan</div>
                    <div class="space-y-1">
                        <Link v-if="isSuperAdmin" :href="route('admin.index')" :class="isActive('admin') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'" class="flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl transition-all duration-200 group" @click="$emit('close')">
                            <svg class="w-5 h-5 mr-3 transition-colors" :class="isActive('admin') ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-500'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Kelola User
                        </Link>

                        <a v-if="$page.props.global_settings.support_whatsapp" :href="'https://wa.me/' + $page.props.global_settings.support_whatsapp + '?text=Halo, saya butuh bantuan terkait aplikasi PPG.'" target="_blank" class="flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl transition-all duration-200 bg-green-50 text-green-700 hover:bg-green-100 group">
                            <svg class="w-5 h-5 mr-3 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                            </svg>
                            Chat Support
                        </a>

                        <Link v-if="user.role === 'developer'" :href="route('settings.index')" :class="isActive('settings') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'" class="flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl transition-all duration-200 group" @click="$emit('close')">
                            <svg class="w-5 h-5 mr-3 transition-colors" :class="isActive('settings') ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-500'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Pengaturan Web
                        </Link>

                        <Link :href="route('import.index')" :class="isActive('import') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'" class="flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl transition-all duration-200 group" @click="$emit('close')">
                             <svg class="w-5 h-5 mr-3 transition-colors" :class="isActive('import') ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-500'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                             Import Data
                        </Link>
                    </div>
                </div>
            </nav>

            <!-- User Footer -->
            <div class="p-4 border-t border-gray-100 bg-white">
                <div class="flex items-center gap-3 p-2 rounded-xl bg-white border border-gray-100 shadow-sm">
                    <div class="w-11 h-11 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-sm uppercase shadow-sm">
                        {{ user.name?.charAt(0) || 'U' }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-gray-900 truncate leading-none mb-1" :title="user.name">{{ user.name || 'User' }}</p>
                        <p class="text-[10px] font-medium text-gray-500 truncate uppercase tracking-tighter">{{ user.scope_label || user.email || '' }}</p>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
