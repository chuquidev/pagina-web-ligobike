<script setup lang="ts">
import { computed } from 'vue'
import { RefreshCw } from '@lucide/vue'
import { useSettingsStore } from '@/stores/settings'
import { useCatalogStore } from '@/stores/catalog'
import HeroSlider from '@/components/HeroSlider.vue'
import CategoryCard from '@/components/CategoryCard.vue'
import ProductCard from '@/components/ProductCard.vue'
import SkeletonCard from '@/components/SkeletonCard.vue'

const settingsStore = useSettingsStore()
const catalogStore = useCatalogStore()

const loading = computed(() => !catalogStore.loaded && !catalogStore.error)
</script>

<template>
    <div>
        <h1 class="sr-only">
            {{ settingsStore.settings?.store_name ? `${settingsStore.settings.store_name} — Bicicletas, repuestos y
            accesorios en Chiclayo` : 'Bicicletas, repuestos y accesorios en Chiclayo' }}
        </h1>
        <HeroSlider v-if="catalogStore.banners.length" :banners="catalogStore.banners" />

        <section v-else-if="!loading && !catalogStore.error"
            class="relative overflow-hidden py-16 text-white sm:py-20 lg:py-28">
            <div class="absolute inset-0"
                style="background: linear-gradient(135deg, var(--color-brand-primary), var(--color-brand-secondary))">
            </div>
            <div class="relative mx-auto max-w-[1400px] px-4 text-center">
                <p class="text-sm font-medium uppercase tracking-widest text-white/70">
                    {{ settingsStore.settings?.store_name }}
                </p>
                <h1 class="mt-3 font-display text-3xl font-bold sm:text-4xl lg:text-5xl">
                    Encuentra lo que buscas,<br class="hidden sm:block" /> consulta al instante
                </h1>
                <p class="mx-auto mt-4 max-w-xl text-sm text-white/80 sm:text-base">
                    Explora el catálogo y escríbenos directo por WhatsApp — sin registros, sin vueltas.
                </p>
                <RouterLink to="/catalogo"
                    class="mt-8 inline-flex items-center gap-2 rounded-full bg-white px-6 py-3 font-display font-semibold text-gray-900 transition hover:bg-gray-100">
                    Ver catálogo
                </RouterLink>
            </div>
        </section>

        <section v-if="loading" class="mx-auto max-w-[1400px] px-4 py-10 sm:py-12">
            <div class="h-6 w-40 animate-pulse rounded bg-gray-200 dark:bg-gray-800"></div>
            <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-4 xl:grid-cols-5">
                <SkeletonCard v-for="n in 5" :key="n" />
            </div>
        </section>

        <div v-else-if="catalogStore.error"
            class="mx-auto flex max-w-[1400px] flex-col items-center gap-3 px-4 py-24 text-center">
            <p class="text-gray-500 dark:text-gray-400">No pudimos cargar la página. Revisa tu conexión e inténtalo de
                nuevo.</p>
            <button type="button"
                class="flex items-center gap-2 rounded-full bg-brand-primary px-5 py-2.5 text-sm font-semibold text-white hover:brightness-110"
                @click="catalogStore.fetch()">
                <RefreshCw class="h-4 w-4" />
                Reintentar
            </button>
        </div>

        <template v-else>
            <section v-if="catalogStore.categories.length" class="mx-auto max-w-[1400px] px-4 py-10 sm:py-12">
                <div class="flex items-center gap-2">
                    <h2
                        class="font-display text-lg font-bold uppercase tracking-wide text-gray-900 dark:text-gray-100 sm:text-xl">
                        Conoce nuestras categorías</h2>
                </div>
                <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-3 sm:gap-5 lg:grid-cols-6">
                    <CategoryCard v-for="category in catalogStore.categories" :key="category.id" :category="category" />
                </div>
            </section>

            <section v-if="catalogStore.featuredProducts.length" class="mx-auto max-w-[1400px] px-4 pb-12 sm:pb-16">
                <h2 class="font-display text-lg font-semibold text-gray-900 dark:text-gray-100 sm:text-xl">Destacados
                </h2>
                <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-4 xl:grid-cols-5">
                    <ProductCard v-for="product in catalogStore.featuredProducts" :key="product.id"
                        :product="product" />
                </div>
            </section>

            <div v-if="!catalogStore.featuredProducts.length && !catalogStore.categories.length"
                class="mx-auto max-w-[1400px] px-4 py-24 text-center text-gray-400 dark:text-gray-500">
                Aún no hay productos publicados.
            </div>
        </template>
    </div>
</template>