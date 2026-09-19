<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import { onClickOutside, useDebounceFn } from '@vueuse/core'
import { Search, Package, FolderTree, Tags, Loader2 } from '@lucide/vue'
import { catalogService } from '@/services/catalog.service'
import { useCatalogStore } from '@/stores/catalog'
import type { Product } from '@/types/catalog'

const props = withDefaults(defineProps<{ autofocus?: boolean }>(), { autofocus: false })
const emit = defineEmits<{ navigated: [] }>()

const router = useRouter()
const catalogStore = useCatalogStore()

const query = ref('')
const open = ref(false)
const loading = ref(false)
const products = ref<Product[]>([])
const containerRef = ref<HTMLElement | null>(null)

onClickOutside(containerRef, () => (open.value = false))

const matchingCategories = computed(() => {
    const term = query.value.trim().toLowerCase()
    if (term.length < 2) return []
    return catalogStore.categories.filter((c) => c.name.toLowerCase().includes(term)).slice(0, 3)
})

const matchingBrands = computed(() => {
    const term = query.value.trim().toLowerCase()
    if (term.length < 2) return []
    return catalogStore.brands.filter((b) => b.name.toLowerCase().includes(term)).slice(0, 3)
})

const hasResults = computed(
    () => products.value.length > 0 || matchingCategories.value.length > 0 || matchingBrands.value.length > 0
)

const runSearch = useDebounceFn(async () => {
    const term = query.value.trim()
    if (term.length < 2) {
        products.value = []
        loading.value = false
        return
    }
    loading.value = true
    try {
        const response = await catalogService.getProducts({ search: term, sort: 'newest' })
        products.value = response.data.slice(0, 5)
    } finally {
        loading.value = false
    }
}, 300)

function onInput() {
    open.value = true
    if (query.value.trim().length < 2) {
        products.value = []
        return
    }
    loading.value = true
    runSearch()
}

function goTo(path: string, queryParams?: Record<string, string>) {
    router.push({ path, query: queryParams })
    open.value = false
    query.value = ''
    emit('navigated')
}

function submit() {
    if (!query.value.trim()) return
    goTo('/catalogo', { search: query.value.trim() })
}
</script>

<template>
    <div ref="containerRef" class="relative w-full">
        <form class="flex items-center gap-2 rounded-full border border-gray-300 px-3 py-1.5 dark:border-gray-700"
            @submit.prevent="submit">
            <Search class="h-4 w-4 shrink-0 text-gray-400" />
            <input v-model="query" type="search" placeholder="Buscar por nombre, categoría o marca..."
                :autofocus="autofocus"
                class="w-full bg-transparent text-sm text-gray-900 outline-none placeholder:text-gray-400 dark:text-gray-100"
                @input="onInput" @focus="open = true" />
            <Loader2 v-if="loading" class="h-4 w-4 shrink-0 animate-spin text-gray-400" />
        </form>

        <div v-if="open && query.trim().length >= 2"
            class="absolute left-0 right-0 top-full z-50 mt-2 max-h-[70vh] overflow-y-auto rounded-2xl border border-gray-200 bg-white p-2 shadow-xl dark:border-gray-800 dark:bg-gray-900">
            <template v-if="hasResults">
                <div v-if="matchingCategories.length" class="mb-1">
                    <p
                        class="px-2 py-1 text-[11px] font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">
                        Categorías</p>
                    <button v-for="c in matchingCategories" :key="c.id" type="button"
                        class="flex w-full items-center gap-2.5 rounded-lg px-2 py-2 text-left text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-800"
                        @click="goTo('/catalogo', { category: c.slug })">
                        <FolderTree class="h-4 w-4 shrink-0 text-gray-400" />
                        {{ c.name }}
                    </button>
                </div>

                <div v-if="matchingBrands.length" class="mb-1">
                    <p
                        class="px-2 py-1 text-[11px] font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">
                        Marcas</p>
                    <button v-for="b in matchingBrands" :key="b.id" type="button"
                        class="flex w-full items-center gap-2.5 rounded-lg px-2 py-2 text-left text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-800"
                        @click="goTo('/catalogo', { brand: b.slug })">
                        <Tags class="h-4 w-4 shrink-0 text-gray-400" />
                        {{ b.name }}
                    </button>
                </div>

                <div v-if="products.length">
                    <p
                        class="px-2 py-1 text-[11px] font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">
                        Productos</p>
                    <button v-for="p in products" :key="p.id" type="button"
                        class="flex w-full items-center gap-3 rounded-lg px-2 py-2 text-left hover:bg-gray-50 dark:hover:bg-gray-800"
                        @click="goTo(`/producto/${p.slug}`)">
                        <div class="h-10 w-10 shrink-0 overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-800">
                            <img v-if="p.images[0]" :src="p.images[0].thumb" class="h-full w-full object-cover" />
                            <div v-else
                                class="flex h-full w-full items-center justify-center text-gray-300 dark:text-gray-600">
                                <Package class="h-4 w-4" />
                            </div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm text-gray-900 dark:text-gray-100">{{ p.name }}</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500">S/ {{ p.sale_price ?? p.price }}</p>
                        </div>
                    </button>
                </div>

                <button type="button"
                    class="mt-1 w-full rounded-lg px-2 py-2 text-left text-sm font-semibold text-brand-primary hover:bg-gray-50 dark:hover:bg-gray-800"
                    @click="submit">
                    Ver todos los resultados para "{{ query.trim() }}"
                </button>
            </template>

            <p v-else-if="!loading" class="px-2 py-4 text-center text-sm text-gray-400 dark:text-gray-500">
                Sin resultados para "{{ query.trim() }}"
            </p>
        </div>
    </div>
</template>