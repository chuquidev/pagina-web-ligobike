<script setup lang="ts">
defineProps<{ items: { label: string; to?: string }[] }>()
</script>

<template>
    <nav aria-label="Breadcrumb"
        class="mb-4 flex flex-wrap items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400 sm:text-sm">
        <template v-for="(item, i) in items" :key="i">
            <RouterLink v-if="item.to" :to="item.to" class="hover:text-brand-primary">{{ item.label }}</RouterLink>
            <span v-else class="text-gray-700 dark:text-gray-300">{{ item.label }}</span>
            <span v-if="i < items.length - 1" class="text-gray-300 dark:text-gray-600">/</span>
        </template>
    </nav>
</template>