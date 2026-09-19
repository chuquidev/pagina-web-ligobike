<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useHead } from '@unhead/vue'
import { catalogService } from '@/services/catalog.service'
import Breadcrumbs from '@/components/Breadcrumbs.vue'
import FaqAccordionItem from '@/components/FaqAccordionItem.vue'
import type { Faq } from '@/types/catalog'

useHead(() => ({ title: 'Preguntas frecuentes' }))

const faqs = ref<Faq[]>([])
const loading = ref(true)

onMounted(async () => {
    faqs.value = await catalogService.getFaqs()
    loading.value = false
})
</script>

<template>
    <div class="mx-auto max-w-[1400px] px-4 py-6 sm:py-8">
        <Breadcrumbs :items="[{ label: 'Inicio', to: '/' }, { label: 'Preguntas frecuentes' }]" />

        <h1 class="font-display text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">Preguntas frecuentes
        </h1>

        <div v-if="loading" class="mt-8 space-y-4">
            <div v-for="n in 4" :key="n" class="h-14 animate-pulse rounded-lg bg-gray-100 dark:bg-gray-800"></div>
        </div>
        <div v-else-if="faqs.length" class="mt-6">
            <FaqAccordionItem v-for="faq in faqs" :key="faq.id" :question="faq.question" :answer="faq.answer" />
        </div>
        <p v-else class="mt-8 text-gray-400 dark:text-gray-500">Aún no hay preguntas publicadas.</p>
    </div>
</template>