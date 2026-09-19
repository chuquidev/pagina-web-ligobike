<script setup lang="ts">
withDefaults(
    defineProps<{
        label: string
        value: string | number
        icon: object
        valueClass?: string
    }>(),
    { valueClass: 'text-gray-900 dark:text-gray-100' }
)
</script>

<template>
    <div class="p-4 sm:p-5">
        <div class="flex items-center gap-1.5 text-gray-400 dark:text-gray-500">
            <component :is="icon" class="h-3.5 w-3.5 shrink-0" />
            <span class="truncate text-xs font-medium">{{ label }}</span>
        </div>
        <p class="mt-2 font-display text-xl font-bold sm:text-2xl" :class="valueClass">{{ value }}</p>
    </div>
</template>