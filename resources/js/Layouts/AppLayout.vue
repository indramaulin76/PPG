<script setup>
import { ref } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import Sidebar from '@/Components/Sidebar.vue';

defineProps({
    title: String,
});

const page = usePage();
const authUser = page.props.auth?.user;

const sidebarOpen = ref(false);
</script>

<template>
    <div class="min-h-screen bg-gray-50 font-sans antialiased text-gray-900">
        <Head :title="title ? `${title} - ${$page.props.global_settings.app_name}` : $page.props.global_settings.app_name" />

        <!-- Mobile Overlay -->
        <div 
            v-if="sidebarOpen"
            class="fixed inset-0 z-40 bg-gray-900/50 lg:hidden"
            @click="sidebarOpen = false"
        ></div>

        <div class="flex h-screen overflow-hidden">
            <!-- Sidebar -->
            <Sidebar :isOpen="sidebarOpen" @close="sidebarOpen = false" />

            <!-- Main Content Area -->
            <div class="flex-1 flex flex-col min-w-0 transition-all duration-300">
                <!-- Top Header -->
                <header class="bg-white/80 backdrop-blur-md border-b border-gray-100 min-h-[4rem] py-3 lg:py-0 flex items-center justify-between px-4 sm:px-5 lg:px-6 sticky top-0 z-30">
                    <!-- Mobile Menu Button -->
                    <div class="flex items-center gap-3">
                        <button 
                            class="lg:hidden text-gray-500 hover:text-gray-700 p-2 -ml-2 rounded-lg hover:bg-gray-100 transition-colors"
                            @click="sidebarOpen = true"
                        >
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>

                        <!-- Page Title -->
                        <div class="flex-1 min-w-0" v-if="$slots.header">
                            <slot name="header" />
                        </div>
                    </div>

                    <!-- Right Settings -->
                    <div class="flex items-center gap-2 sm:gap-4 shrink-0 pl-2">
                         <div class="flex items-center gap-2 sm:gap-3">
                             <div class="text-right hidden md:block">
                                 <p class="text-sm font-semibold text-gray-900 leading-tight">{{ authUser?.name || 'User' }}</p>
                                 <p class="text-[10px] text-gray-500 uppercase tracking-wider font-bold">{{ authUser?.role?.replace('_', ' ') || 'Guest' }}</p>
                             </div>
                             
                             <div class="flex items-center gap-1 sm:gap-2">
                                <Link
                                    :href="route('logout')"
                                    method="post"
                                    as="button"
                                    class="p-2 sm:px-4 bg-gray-50 hover:bg-red-50 text-gray-600 hover:text-red-600 rounded-xl font-medium transition-all duration-200 border border-gray-100 flex items-center"
                                    title="Keluar"
                                >
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                    <span class="hidden lg:inline ml-2 text-sm">Keluar</span>
                                </Link>

                                <div class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-sm shadow-sm ring-2 ring-white">
                                    {{ authUser?.name?.charAt(0).toUpperCase() || 'U' }}
                                </div>
                             </div>
                         </div>
                    </div>
                </header>

                <!-- Page Content -->
                <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50/50 px-3 pt-3 pb-6 sm:px-5 sm:pt-4 sm:pb-8 lg:px-6 lg:pt-5 lg:pb-10">
                    <div class="w-full">
                        <slot />
                    </div>
                </main>
            </div>
        </div>
    </div>
</template>
