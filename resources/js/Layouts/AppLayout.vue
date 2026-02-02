<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import Sidebar from '@/Components/Sidebar.vue';

defineProps({
    title: String,
});

const page = usePage();
const authUser = page.props.auth?.user;
</script>

<template>
    <div class="min-h-screen bg-gray-50 font-sans antialiased text-gray-900">
        <Head :title="title" />

        <div class="flex h-screen overflow-hidden">
            <!-- Sidebar -->
            <Sidebar />

            <!-- Main Content Area -->
            <div class="flex-1 flex flex-col lg:pl-64 transition-all duration-300">
                <!-- Top Header -->
                <header class="bg-white/80 backdrop-blur-md border-b border-gray-100 h-16 flex items-center justify-between px-4 sm:px-6 lg:px-8">
                    <!-- Mobile Menu Button -->
                    <button class="lg:hidden text-gray-500 hover:text-gray-700">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <!-- Page Title -->
                    <h1 class="text-xl font-semibold text-gray-800" v-if="$slots.header">
                        <slot name="header" />
                    </h1>
                    <div v-else></div>

                    <!-- Right Settings -->
                    <div class="flex items-center gap-4">
                         <div class="flex items-center gap-3">
                             <div class="text-right hidden md:block">
                                 <p class="text-sm font-medium text-gray-900">{{ authUser?.name || 'User' }}</p>
                                 <p class="text-xs text-gray-500 uppercase">{{ authUser?.role || 'Guest' }}</p>
                             </div>
                             <Link
                                 :href="route('logout')"
                                 method="post"
                                 as="button"
                                 class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-medium transition-colors"
                             >
                                 <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                 </svg>
                                 <span class="hidden md:inline">Keluar</span>
                             </Link>
                             <div class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-sm shadow-md">
                                {{ authUser?.name?.charAt(0).toUpperCase() || 'U' }}
                             </div>
                        </div>
                    </div>
                </header>

                <!-- Page Content -->
                <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-4 sm:p-6 lg:p-8">
                    <slot />
                </main>
            </div>
        </div>
    </div>
</template>
