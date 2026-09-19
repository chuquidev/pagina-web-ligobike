<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { X } from '@lucide/vue'
import { catalogService, type ProductFilters } from '@/services/catalog.service'
import { useCatalogStore } from '@/stores/catalog'
import ProductCard from '@/components/ProductCard.vue'
import SkeletonCard from '@/components/SkeletonCard.vue'
import Breadcrumbs from '@/components/Breadcrumbs.vue'
import Pagination from '@/components/Pagination.vue'
import type { Product } from '@/types/catalog'

const route = useRoute()
const router = useRouter()
const catalogStore = useCatalogStore()

const products = ref<Product[]>([])
const total = ref(0)
const currentPage = ref(1)
const lastPage = ref(1)
const loading = ref(false)

const sortOptions = [
    { value: 'newest', label: 'Más recientes' },
    { value: 'price_asc', label: 'Precio: menor a mayor' },
    { value: 'price_desc', label: 'Precio: mayor a menor' },
    { value: 'name', label: 'Nombre A-Z' },
]

const breadcrumbItems = computed(() => {
    const items: { label: string; to?: string }[] = [{ label: 'Inicio', to: '/' }, { label: 'Catálogo', to: '/catalogo' }]
    const categorySlug = route.query.category as string | undefined
    if (categorySlug) {
        const category = catalogStore.categories.find((c) => c.slug === categorySlug)
        if (category) items.push({ label: category.name })
    }
    return items
})

async function loadProducts() {
    loading.value = true
    const response = await catalogService.getProducts({
        category: (route.query.category as string) || undefined,
        brand: (route.query.brand as string) || undefined,
        search: (route.query.search as string) || undefined,
        sort: (route.query.sort as ProductFilters['sort']) || 'newest',
        page: Number(route.query.page) || 1,
    })
    products.value = response.data
    total.value = response.meta.total
    currentPage.value = response.meta.current_page
    lastPage.value = response.meta.last_page
    loading.value = false
}

function updateFilter(partial: Record<string, string | undefined>) {
    router.push({ query: { ...route.query, ...partial, page: undefined } })
}

function updatePage(page: number) {
    router.push({ query: { ...route.query, page } })
    window.scrollTo({ top: 0, behavior: 'smooth' })
}

watch(() => route.query, loadProducts, { immediate: true, deep: true })
</script>

<template>
    <div class="mx-auto max-w-[1400px] px-4 py-6 sm:py-8">
        <Breadcrumbs :items="breadcrumbItems" />

        <h1 class="font-display text-xl font-bold text-gray-900 dark:text-gray-100 sm:text-2xl">Catálogo</h1>

        <div class="relative mt-5 sm:mt-6">
            <div
                class="no-scrollbar -mx-4 flex flex-nowrap items-center gap-2 overflow-x-auto px-4 pb-1 sm:mx-0 sm:flex-wrap sm:gap-3 sm:overflow-visible sm:px-0 sm:pb-0">
                <select :value="route.query.category ?? ''"
                    class="shrink-0 rounded-full border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 sm:px-4"
                    @change="updateFilter({ category: ($event.target as HTMLSelectElement).value || undefined })">
                    <option value="">Todas las categorías</option>
                    <option v-for="c in catalogStore.categories" :key="c.id" :value="c.slug">{{ c.name }}</option>
                </select>

                <select :value="route.query.brand ?? ''"
                    class="shrink-0 rounded-full border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 sm:px-4"
                    @change="updateFilter({ brand: ($event.target as HTMLSelectElement).value || undefined })">
                    <option value="">Todas las marcas</option>
                    <option v-for="b in catalogStore.brands" :key="b.id" :value="b.slug">{{ b.name }}</option>
                </select>

                <select :value="route.query.sort ?? 'newest'"
                    class="shrink-0 rounded-full border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 sm:px-4"
                    @change="updateFilter({ sort: ($event.target as HTMLSelectElement).value })">
                    <option v-for="opt in sortOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                </select>

                <span v-if="route.query.search"
                    class="flex shrink-0 items-center gap-2 rounded-full bg-gray-100 px-4 py-2 text-sm text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                    "{{ route.query.search }}"
                    <button class="text-gray-400 hover:text-gray-700 dark:hover:text-gray-200"
                        @click="updateFilter({ search: undefined })">
                        <X class="h-3.5 w-3.5" />
                    </button>
                </span>
            </div>

            <!-- Degradado que insinúa que hay más filtros hacia la derecha (solo móvil) -->
            <div
                class="pointer-events-none absolute top-0 right-0 h-full w-8 bg-gradient-to-l from-white to-transparent dark:from-gray-950 sm:hidden">
            </div>
        </div>

        <div v-if="loading" class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-4 xl:grid-cols-5">
            <SkeletonCard v-for="n in 10" :key="n" />
        </div>
        <div v-else-if="!products.length" class="py-24 text-center text-gray-400 dark:text-gray-500">
            No encontramos productos con esos filtros.
        </div>

        <div v-else class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-4 xl:grid-cols-5">
            <ProductCard v-for="product in products" :key="product.id" :product="product" />
        </div>

        <Pagination :current-page="currentPage" :last-page="lastPage" :total="total" @change="updatePage" />
    </div>
</template>