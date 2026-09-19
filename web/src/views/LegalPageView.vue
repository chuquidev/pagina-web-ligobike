<script setup lang="ts">
import { computed } from 'vue'
import { useSettingsStore } from '@/stores/settings'
import { useHead } from '@unhead/vue'
import Breadcrumbs from '@/components/Breadcrumbs.vue'

const props = defineProps<{ field: 'privacy_policy' | 'terms_conditions'; title: string }>()
const settingsStore = useSettingsStore()

useHead(() => ({ title: props.title }))

const paragraphs = computed(() => {
    const content = settingsStore.settings?.[props.field]
    if (!content) return []
    return content.split(/\n\s*\n/).map((p) => p.trim()).filter(Boolean)
})
</script>

<template>
    <div class="mx-auto max-w-[1400px] px-4 py-6 sm:py-8">
        <Breadcrumbs :items="[{ label: 'Inicio', to: '/' }, { label: title }]" />

        <h1 class="font-display text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">{{ title }}</h1>

        <div v-if="paragraphs.length" class="mt-6 space-y-4">
            <p v-for="(paragraph, i) in paragraphs" :key="i" class="leading-relaxed text-gray-600 dark:text-gray-400">{{
                paragraph }}</p>
        </div>
        <p v-else class="mt-6 text-gray-400 dark:text-gray-500">Aún no se ha publicado este contenido.</p>
    </div>
</template>