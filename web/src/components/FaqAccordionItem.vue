<script setup lang="ts">
import { ref } from 'vue'
import { ChevronDown } from '@lucide/vue'

defineProps<{ question: string; answer: string }>()
const open = ref(false)
</script>

<template>
    <div class="border-b border-gray-200 py-4 dark:border-gray-800">
        <button class="flex w-full items-center justify-between gap-4 text-left" @click="open = !open">
            <span class="font-medium text-gray-900 dark:text-gray-100">{{ question }}</span>
            <ChevronDown class="h-4 w-4 shrink-0 text-gray-400 transition-transform" :class="{ 'rotate-180': open }" />
        </button>
        <p v-if="open" class="mt-3 text-sm leading-relaxed text-gray-600 dark:text-gray-400">{{ answer }}</p>
    </div>
</template>