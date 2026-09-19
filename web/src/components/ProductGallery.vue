<script setup lang="ts">
import { ref } from 'vue'
import { Package, Maximize2 } from '@lucide/vue'
import type { ProductImage } from '@/types/catalog'
import DiscountBadge from './DiscountBadge.vue'
import ProductLightbox from './ProductLightbox.vue'

defineProps<{
    images: ProductImage[]
    alt: string
    price?: string
    salePrice?: string | null
}>()
const activeIndex = ref(0)
const lightboxOpen = ref(false)
</script>

<template>
    <div>
        <div class="relative aspect-square overflow-hidden rounded-2xl bg-gray-100 dark:bg-gray-800">
            <button v-if="images.length" class="relative h-full w-full" aria-label="Ver imagen ampliada"
                @click="lightboxOpen = true">
                <img :src="images[activeIndex].large" :alt="alt" class="h-full w-full cursor-zoom-in object-cover"
                    fetchpriority="high" loading="eager" decoding="sync" />
                <span
                    class="absolute bottom-2 right-2 flex h-9 w-9 items-center justify-center rounded-full bg-black/50 text-white">
                    <Maximize2 class="h-4 w-4" />
                </span>
            </button>
            <div v-else class="flex h-full w-full items-center justify-center text-gray-300 dark:text-gray-600">
                <Package class="h-16 w-16" />
            </div>
            <DiscountBadge v-if="price && salePrice" :price="price" :sale-price="salePrice" />
        </div>

        <div v-if="images.length > 1" class="mt-3 flex gap-2 overflow-x-auto pb-1">
            <button v-for="(image, index) in images" :key="image.id"
                class="h-14 w-14 shrink-0 overflow-hidden rounded-lg border-2 transition sm:h-16 sm:w-16"
                :class="index === activeIndex ? 'border-brand-primary' : 'border-transparent'"
                @click="activeIndex = index">
                <img :src="image.thumb" :alt="`${alt} ${index + 1}`" class="h-full w-full object-cover" />
            </button>
        </div>

        <ProductLightbox v-if="lightboxOpen" :images="images" v-model="activeIndex" :alt="alt"
            @close="lightboxOpen = false" />
    </div>
</template>