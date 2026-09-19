<script setup lang="ts">
import { computed } from 'vue'
import type { Availability } from '@/types/catalog'

const props = defineProps<{ availability: Availability }>()

const config = computed(() => {
    switch (props.availability) {
        case 'in_stock':
            return {
                label: 'Disponible',
                dot: 'bg-green-500',
                text: 'text-green-700 dark:text-green-400',
                bg: 'bg-green-50 dark:bg-green-950/40',
            }
        case 'on_request':
            return {
                label: 'Por encargo',
                dot: 'bg-amber-500',
                text: 'text-amber-700 dark:text-amber-400',
                bg: 'bg-amber-50 dark:bg-amber-950/40',
            }
        default:
            return {
                label: 'Agotado',
                dot: 'bg-gray-400',
                text: 'text-gray-600 dark:text-gray-400',
                bg: 'bg-gray-100 dark:bg-gray-800',
            }
    }
})
</script>

<template>
    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium"
        :class="[config.bg, config.text]">
        <span class="h-1.5 w-1.5 rounded-full" :class="config.dot"></span>
        {{ config.label }}
    </span>
</template>