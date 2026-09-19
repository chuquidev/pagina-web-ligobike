<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
    data: { label: string; value: number; color: string }[]
}>()

const total = computed(() => props.data.reduce((sum, d) => sum + d.value, 0))

const segments = computed(() => {
    const radius = 40
    const circumference = 2 * Math.PI * radius
    let offset = 0
    return props.data.map((d) => {
        const fraction = total.value > 0 ? d.value / total.value : 0
        const dash = fraction * circumference
        const segment = {
            ...d,
            dasharray: `${dash} ${circumference - dash}`,
            dashoffset: -offset,
            percent: total.value > 0 ? Math.round(fraction * 100) : 0,
        }
        offset += dash
        return segment
    })
})
</script>

<template>
    <div v-if="total > 0" class="flex flex-col items-center gap-6 sm:flex-row sm:gap-10">
        <div class="relative shrink-0">
            <svg viewBox="0 0 100 100" class="h-44 w-44 -rotate-90 sm:h-52 sm:w-52">
                <circle cx="50" cy="50" r="40" fill="none" stroke-width="12"
                    class="stroke-gray-100 dark:stroke-gray-800" />
                <circle v-for="(segment, i) in segments" :key="i" cx="50" cy="50" r="40" fill="none"
                    :stroke="segment.color" stroke-width="12" stroke-linecap="round"
                    :stroke-dasharray="segment.dasharray" :stroke-dashoffset="segment.dashoffset"
                    class="transition-all duration-500" />
            </svg>
            <div class="absolute inset-0 flex flex-col items-center justify-center">
                <span class="font-display text-3xl font-bold text-gray-900 dark:text-gray-100 sm:text-4xl">{{ total
                }}</span>
                <span class="text-xs text-gray-400 dark:text-gray-500">producto{{ total === 1 ? '' : 's' }}</span>
            </div>
        </div>
        <ul class="w-full space-y-3 text-sm sm:w-auto">
            <li v-for="(segment, i) in segments" :key="i" class="flex items-center gap-2.5">
                <span class="h-3 w-3 shrink-0 rounded-full" :style="{ backgroundColor: segment.color }"></span>
                <span class="text-gray-600 dark:text-gray-300">{{ segment.label }}</span>
                <span class="font-semibold text-gray-900 dark:text-gray-100">{{ segment.value }}</span>
                <span class="text-gray-400 dark:text-gray-500">({{ segment.percent }}%)</span>
            </li>
        </ul>
    </div>
    <p v-else class="text-sm text-gray-400 dark:text-gray-500">Aún no hay productos para mostrar.</p>
</template>