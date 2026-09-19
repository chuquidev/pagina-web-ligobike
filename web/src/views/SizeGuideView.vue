<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useHead } from '@unhead/vue'
import { useSettingsStore } from '@/stores/settings'
import Breadcrumbs from '@/components/Breadcrumbs.vue'

useHead(() => ({ title: 'Guía de tallas' }))

const settingsStore = useSettingsStore()

onMounted(() => settingsStore.fetch())

// Divide la tabla en dos columnas (la primera mitad de filas a la izquierda,
// el resto a la derecha) para no dejar tanto espacio vacío en pantallas grandes.
const leftRows = computed(() => {
    const rows = settingsStore.settings?.size_guide ?? []
    return rows.slice(0, Math.ceil(rows.length / 2))
})

const rightRows = computed(() => {
    const rows = settingsStore.settings?.size_guide ?? []
    return rows.slice(Math.ceil(rows.length / 2))
})
</script>

<template>
    <div class="mx-auto max-w-[1400px] px-4 py-6 sm:py-8">
        <Breadcrumbs :items="[{ label: 'Inicio', to: '/' }, { label: 'Guía de tallas' }]" />

        <h1 class="font-display text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">Guía de tallas</h1>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
            Usa tu estatura como referencia para elegir el tamaño de cuadro adecuado. Si tienes dudas, escríbenos por
            WhatsApp y te orientamos.
        </p>

        <div v-if="settingsStore.settings?.size_guide.length" class="mt-6 grid gap-4 sm:grid-cols-2">
            <div class="overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-800">
                <table class="w-full text-left text-sm">
                    <thead
                        class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-800/60 dark:text-gray-400">
                        <tr>
                            <th class="px-4 py-3">Estatura</th>
                            <th class="px-4 py-3">Talla recomendada</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-800 dark:bg-gray-900">
                        <tr v-for="(row, i) in leftRows" :key="i">
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ row.height }}</td>
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">{{ row.size }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="rightRows.length"
                class="overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-800">
                <table class="w-full text-left text-sm">
                    <thead
                        class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-800/60 dark:text-gray-400">
                        <tr>
                            <th class="px-4 py-3">Estatura</th>
                            <th class="px-4 py-3">Talla recomendada</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-800 dark:bg-gray-900">
                        <tr v-for="(row, i) in rightRows" :key="i">
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ row.height }}</td>
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">{{ row.size }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <p v-else class="mt-6 text-gray-400 dark:text-gray-500">Aún no se ha publicado la guía de tallas.</p>
    </div>
</template>