<script setup lang="ts">
import { Package } from '@lucide/vue'
import type { Category } from '@/types/catalog'

defineProps<{ category: Category }>()
</script>

<template>
    <RouterLink :to="{ path: '/catalogo', query: { category: category.slug } }" class="group block">
        <div class="relative aspect-square overflow-hidden rounded-2xl bg-gray-100 dark:bg-gray-800">
            <img v-if="category.image" :src="category.image" :alt="category.name" loading="lazy" decoding="async"
                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" />
            <div v-else
                class="flex h-full w-full items-center justify-center bg-gradient-to-br from-brand-primary to-brand-secondary">
                <Package class="h-10 w-10 text-white/60" />
            </div>

            <div class="absolute inset-x-0 top-0 bg-gradient-to-b from-black/70 to-black/0 p-3 pb-8">
                <div class="flex items-center gap-1.5">
                    <span
                        class="font-display text-base font-extrabold uppercase leading-tight tracking-wide text-white drop-shadow sm:text-lg">
                        {{ category.name }}
                    </span>
                </div>
            </div>
        </div>
    </RouterLink>
</template>