<script setup lang="ts">
import { formatCurrency } from '@/utils/currency'

defineProps<{
    price: string
    salePrice?: string | null
}>()
</script>

<template>
    <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
        <div class="price-tag flex items-center gap-1.5 bg-brand-primary py-1.5 pl-4 pr-3 text-white">
            <span class="price-tag-hole"></span>
            <span class="font-display text-base font-semibold leading-none sm:text-lg">
                {{ formatCurrency(salePrice ?? price) }}
            </span>
        </div>

        <span v-if="salePrice" class="text-xs text-gray-400 line-through dark:text-gray-500 sm:text-sm">
            {{ formatCurrency(price) }}
        </span>
    </div>
</template>

<style scoped>
.price-tag {
    clip-path: polygon(12px 0, 100% 0, 100% 100%, 12px 100%, 0 50%);
}

.price-tag-hole {
    width: 4px;
    height: 4px;
    border-radius: 9999px;
    background: rgb(255 255 255 / 0.6);
}
</style>