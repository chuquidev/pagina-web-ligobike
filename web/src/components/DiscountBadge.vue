<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{ price: string; salePrice: string }>()

const percent = computed(() => Math.round((1 - Number(props.salePrice) / Number(props.price)) * 100))
</script>

<template>
    <span
        class="pointer-events-none absolute left-2 top-2 z-20 rounded-md bg-red-600 px-2 py-1 text-xs font-bold text-white shadow-sm">
        -{{ percent }}%
    </span>
</template>