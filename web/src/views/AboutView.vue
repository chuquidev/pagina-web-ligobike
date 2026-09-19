<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useHead } from '@unhead/vue'
import { useSettingsStore } from '@/stores/settings'
import Breadcrumbs from '@/components/Breadcrumbs.vue'

const settingsStore = useSettingsStore()

useHead(() => ({ title: `Nosotros — ${settingsStore.settings?.store_name ?? ''}` }))

const paragraphs = computed(() => {
    const content = settingsStore.settings?.about_content
    if (!content) return []
    return content.split(/\n\s*\n/).map((p) => p.trim()).filter(Boolean)
})

const images = computed(() => settingsStore.settings?.about_images ?? [])

onMounted(() => settingsStore.fetch())
</script>

<template>
    <div class="mx-auto max-w-[1400px] px-4 py-6 sm:py-8">
        <Breadcrumbs :items="[{ label: 'Inicio', to: '/' }, { label: 'Nosotros' }]" />

        <h1 class="font-display text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">
            Sobre {{ settingsStore.settings?.store_name }}
        </h1>

        <div class="mt-8 grid gap-8 lg:grid-cols-2 lg:gap-12 lg:items-start">
            <div class="order-2 lg:order-1">
                <div v-if="paragraphs.length" class="max-w-2xl space-y-4">
                    <p v-for="(p, i) in paragraphs" :key="i" class="leading-relaxed text-gray-600 dark:text-gray-400">{{
                        p }}</p>
                </div>
                <p v-else class="text-gray-400 dark:text-gray-500">Aún no se ha publicado este contenido.</p>
            </div>

            <div v-if="images.length" class="order-1 lg:order-2 lg:sticky lg:top-24">
                <div class="grid gap-4" :class="images.length > 1 ? 'grid-cols-2' : 'grid-cols-1'">
                    <div v-for="img in images" :key="img.id"
                        class="aspect-[4/3] overflow-hidden rounded-2xl bg-gray-100 dark:bg-gray-800"
                        :class="{ 'col-span-2': images.length === 3 && img.id === images[0].id }">
                        <img :src="img.url" class="h-full w-full object-cover" loading="lazy" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>