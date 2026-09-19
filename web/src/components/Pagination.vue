<script setup lang="ts">
import { computed } from 'vue'
import { ChevronLeft, ChevronRight } from '@lucide/vue'

const props = defineProps<{ currentPage: number; lastPage: number; total?: number }>()
const emit = defineEmits<{ change: [page: number] }>()

// Ventana de páginas visibles: primera, última, actual ±1, con "…" en los huecos.
// Evita renderizar un botón por página cuando hay decenas de páginas.
const pages = computed<(number | '...')[]>(() => {
    const last = props.lastPage
    const current = props.currentPage
    if (last <= 7) return Array.from({ length: last }, (_, i) => i + 1)

    const result: (number | '...')[] = [1]
    if (current > 3) result.push('...')

    const start = Math.max(2, current - 1)
    const end = Math.min(last - 1, current + 1)
    for (let p = start; p <= end; p++) result.push(p)

    if (current < last - 2) result.push('...')
    result.push(last)
    return result
})

function go(page: number) {
    if (page < 1 || page > props.lastPage || page === props.currentPage) return
    emit('change', page)
}
</script>

<template>
    <nav v-if="lastPage > 1" class="mt-6 flex flex-col items-center gap-3 sm:flex-row sm:justify-between">
        <p v-if="total !== undefined" class="text-sm text-gray-500 dark:text-gray-400">
            Página <span class="font-medium text-gray-900 dark:text-gray-100">{{ currentPage }}</span> de {{ lastPage }}
            <span class="text-gray-300 dark:text-gray-700">·</span>
            {{ total }} resultado{{ total === 1 ? '' : 's' }}
        </p>

        <div
            class="flex items-center gap-0.5 rounded-full border border-gray-200 bg-white p-1 dark:border-gray-800 dark:bg-gray-900">
            <button type="button" :disabled="currentPage === 1"
                class="flex h-8 items-center gap-1 rounded-full px-3 text-sm font-medium text-gray-600 transition hover:bg-gray-100 disabled:pointer-events-none disabled:opacity-30 dark:text-gray-300 dark:hover:bg-gray-800"
                aria-label="Página anterior" @click="go(currentPage - 1)">
                <ChevronLeft class="h-4 w-4" />
                <span class="hidden sm:inline">Anterior</span>
            </button>

            <template v-for="(page, i) in pages" :key="i">
                <span v-if="page === '...'" class="w-8 text-center text-sm text-gray-400 dark:text-gray-600">…</span>
                <button v-else type="button" class="h-8 min-w-8 rounded-full px-2.5 text-sm font-medium transition"
                    :class="page === currentPage
                        ? 'bg-brand-primary text-white shadow-sm'
                        : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800'"
                    :aria-current="page === currentPage ? 'page' : undefined" @click="go(page)">
                    {{ page }}
                </button>
            </template>

            <button type="button" :disabled="currentPage === lastPage"
                class="flex h-8 items-center gap-1 rounded-full px-3 text-sm font-medium text-gray-600 transition hover:bg-gray-100 disabled:pointer-events-none disabled:opacity-30 dark:text-gray-300 dark:hover:bg-gray-800"
                aria-label="Página siguiente" @click="go(currentPage + 1)">
                <span class="hidden sm:inline">Siguiente</span>
                <ChevronRight class="h-4 w-4" />
            </button>
        </div>
    </nav>
</template>