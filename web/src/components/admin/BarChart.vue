<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
    data: { label: string; value: number }[]
}>()

const max = computed(() => Math.max(1, ...props.data.map((d) => d.value)))
</script>

<template>
    <div v-if="data.length" class="space-y-3">
        <div v-for="(item, i) in data" :key="i">
            <div class="mb-1 flex items-center justify-between text-sm">
                <span class="text-gray-600 dark:text-gray-300">{{ item.label }}</span>
                <span class="font-medium text-gray-900 dark:text-gray-100">{{ item.value }}</span>
            </div>
            <div class="h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                <div class="h-full rounded-full bg-brand-primary transition-all duration-500"
                    :style="{ width: `${(item.value / max) * 100}%` }"></div>
            </div>
        </div>
    </div>
    <p v-else class="text-sm text-gray-400 dark:text-gray-500">Aún no hay categorías con productos.</p>
</template>