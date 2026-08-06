<script setup>
defineProps({
    // [{ name, value, icon, color, shadow }]
    cards: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <div
        class="grid grid-cols-1 gap-4 sm:gap-6 sm:grid-cols-2"
        :class="cards.length >= 4 ? 'lg:grid-cols-4' : 'lg:grid-cols-3'"
    >
        <div
            v-for="card in cards"
            :key="card.name"
            class="group relative overflow-hidden rounded-2xl bg-white p-5 sm:p-6 shadow-sm border border-gray-100 hover:shadow-md transition-all duration-300"
        >
            <div class="flex items-center gap-4">
                <div class="rounded-xl p-3 text-white shadow-lg shrink-0" :class="[card.color, card.shadow]">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path v-if="card.icon === 'users'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        <path v-else-if="card.icon === 'map'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                        <path v-else-if="card.icon === 'collection'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        <path v-else-if="card.icon === 'home'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        <path v-else-if="card.icon === 'scale'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-0.5 truncate">{{ card.name }}</p>
                    <p class="text-2xl font-black text-gray-900 leading-none">{{ card.value }}</p>
                </div>
            </div>
        </div>
    </div>
</template>
