<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { Share2, Check, ArrowLeft, ShoppingCart, Plus, Minus, PackageX } from '@lucide/vue'
import { catalogService } from '@/services/catalog.service'
import { useSettingsStore } from '@/stores/settings'
import { useCartStore } from '@/stores/cart'
import { buildWhatsAppUrl } from '@/utils/whatsapp'
import { canGoBack } from '@/router'
import ProductGallery from '@/components/ProductGallery.vue'
import PriceTag from '@/components/PriceTag.vue'
import AvailabilityBadge from '@/components/AvailabilityBadge.vue'
import Breadcrumbs from '@/components/Breadcrumbs.vue'
import SkeletonProductDetail from '@/components/SkeletonProductDetail.vue'
import ProductCard from '@/components/ProductCard.vue'
import type { Product } from '@/types/catalog'
import { useHead } from '@unhead/vue'
import { trackEvent } from '@/utils/analytics'

const props = defineProps<{ slug: string }>()
const settingsStore = useSettingsStore()
const cartStore = useCartStore()
const router = useRouter()

const product = ref<Product | null>(null)
const relatedProducts = ref<Product[]>([])
const notFound = ref(false)
const loading = ref(true)
const copied = ref(false)
const quantity = ref(1)
const justAdded = ref(false)

// Datos estructurados (schema.org Product) para que Google pueda mostrar
// precio y disponibilidad directamente en los resultados de búsqueda.
const productJsonLd = computed(() => {
    if (!product.value) return null

    const p = product.value
    const finalPrice = Number(p.sale_price ?? p.price)

    return {
        '@context': 'https://schema.org',
        '@type': 'Product',
        name: p.name,
        description: p.description || undefined,
        sku: p.sku || undefined,
        image: p.images.map((img) => img.large),
        brand: p.brand ? { '@type': 'Brand', name: p.brand.name } : undefined,
        offers: {
            '@type': 'Offer',
            url: window.location.href,
            priceCurrency: 'PEN',
            price: finalPrice,
            availability:
                p.availability === 'out_of_stock'
                    ? 'https://schema.org/OutOfStock'
                    : 'https://schema.org/InStock',
        },
    }
})

useHead(() => ({
    title: product.value ? `${product.value.name} — ${settingsStore.settings?.store_name ?? ''}` : 'Producto',
    meta: [
        { name: 'description', content: product.value?.description?.slice(0, 160) ?? 'Consulta este producto por WhatsApp.' },
        { property: 'og:title', content: product.value?.name },
        { property: 'og:description', content: product.value?.description?.slice(0, 160) },
        { property: 'og:image', content: product.value?.images[0]?.large },
        { property: 'og:type', content: 'product' },
    ],
    script: productJsonLd.value
        ? [{ type: 'application/ld+json', innerHTML: JSON.stringify(productJsonLd.value) }]
        : [],
}))

const breadcrumbItems = computed(() => {
    if (!product.value) return []
    return [
        { label: 'Inicio', to: '/' },
        { label: 'Catálogo', to: '/catalogo' },
        { label: product.value.category.name, to: `/catalogo?category=${product.value.category.slug}` },
        { label: product.value.name },
    ]
})

const isOutOfStock = computed(() => product.value?.availability === 'out_of_stock')

async function loadProduct() {
    loading.value = true
    notFound.value = false
    relatedProducts.value = []
    quantity.value = 1
    try {
        product.value = await catalogService.getProductBySlug(props.slug)
        loadRelatedProducts()
    } catch {
        notFound.value = true
    } finally {
        loading.value = false
    }
}

async function loadRelatedProducts() {
    if (!product.value) return
    const response = await catalogService.getProducts({ category: product.value.category.slug, sort: 'newest' })
    relatedProducts.value = response.data.filter((p) => p.id !== product.value?.id).slice(0, 4)
}

function goBack() {
    if (canGoBack()) {
        router.back()
    } else {
        router.push('/catalogo')
    }
}

function addToCart() {
    if (!product.value) return
    cartStore.addItem(product.value, quantity.value)
    justAdded.value = true
    setTimeout(() => (justAdded.value = false), 1500)
}

const whatsappUrl = computed(() => {
    if (!product.value || !settingsStore.settings?.whatsapp_number) return null
    const message = isOutOfStock.value
        ? `Hola, quiero que me avisen cuando esté disponible este producto: ${product.value.name}.\n${window.location.href}`
        : `Hola, me interesa este producto: ${product.value.name}.\n${window.location.href}`
    return buildWhatsAppUrl(settingsStore.settings.whatsapp_number, message)
})

function trackWhatsappClick() {
    if (!product.value) return
    trackEvent('generate_lead', {
        currency: 'PEN',
        value: Number(product.value.sale_price ?? product.value.price),
        source: 'product_page',
        item_name: product.value.name,
    })
}

async function share() {
    const url = window.location.href
    if (navigator.share) {
        await navigator.share({ title: product.value?.name, url })
    } else {
        await navigator.clipboard.writeText(url)
        copied.value = true
        setTimeout(() => (copied.value = false), 2000)
    }
}

onMounted(loadProduct)
watch(() => props.slug, loadProduct)
</script>


<template>
    <div class="mx-auto max-w-[1400px] px-4 py-6 sm:py-10">
        <button
            class="mb-4 flex items-center gap-1.5 text-sm font-medium text-gray-600 hover:text-brand-primary dark:text-gray-300 sm:mb-6"
            @click="goBack">
            <ArrowLeft class="h-4 w-4" />
            Volver
        </button>

        <SkeletonProductDetail v-if="loading" />

        <div v-else-if="notFound" class="flex flex-col items-center px-4 py-16 text-center sm:py-24">
            <div
                class="flex h-20 w-20 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                <PackageX class="h-9 w-9" />
            </div>
            <h1 class="mt-6 font-display text-xl font-bold text-gray-900 dark:text-gray-100 sm:text-2xl">Este producto
                ya no está disponible</h1>
            <p class="mt-2 max-w-sm text-sm text-gray-500 dark:text-gray-400">
                Puede que se haya agotado o que el enlace ya no sea válido. Prueba con el catálogo completo.
            </p>
            <div class="mt-8 flex flex-wrap justify-center gap-3">
                <RouterLink to="/catalogo"
                    class="rounded-full bg-brand-primary px-6 py-2.5 font-display text-sm font-semibold text-white transition hover:brightness-110">
                    Ver catálogo
                </RouterLink>
                <RouterLink to="/"
                    class="rounded-full border border-gray-200 px-6 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                    Ir al inicio
                </RouterLink>
            </div>
        </div>

        <template v-else-if="product">
            <Breadcrumbs :items="breadcrumbItems" />

            <div class="grid gap-8 md:grid-cols-2 md:gap-10">
                <ProductGallery :images="product.images" :alt="product.name" :price="product.price"
                    :sale-price="product.sale_price" />

                <div>
                    <span class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">
                        {{ product.category.name }}<template v-if="product.brand"> · {{ product.brand.name }}</template>
                    </span>
                    <h1 class="mt-1 font-display text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">{{
                        product.name }}</h1>

                    <div class="mt-4">
                        <PriceTag :price="product.price" :sale-price="product.sale_price" />
                    </div>

                    <AvailabilityBadge :availability="product.availability" class="mt-4" />

                    <p v-if="product.description" class="mt-6 leading-relaxed text-gray-600 dark:text-gray-400">{{
                        product.description }}
                    </p>

                    <ul v-if="product.features?.length" class="mt-6 space-y-2">
                        <li v-for="(feature, i) in product.features" :key="i"
                            class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                            <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-brand-primary"></span>
                            {{ feature }}
                        </li>
                    </ul>

                    <div class="mt-8 flex items-center gap-3">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Cantidad</span>
                        <div class="flex items-center gap-2">
                            <button
                                class="flex h-8 w-8 items-center justify-center rounded-full border border-gray-200 text-gray-500 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-800"
                                @click="quantity = Math.max(1, quantity - 1)">
                                <Minus class="h-3.5 w-3.5" />
                            </button>
                            <span class="w-8 text-center text-sm text-gray-900 dark:text-gray-100">{{ quantity }}</span>
                            <button
                                class="flex h-8 w-8 items-center justify-center rounded-full border border-gray-200 text-gray-500 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-800"
                                @click="quantity++">
                                <Plus class="h-3.5 w-3.5" />
                            </button>
                        </div>
                    </div>

                    <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                        <a v-if="whatsappUrl" :href="whatsappUrl" target="_blank" rel="noopener"
                            @click="trackWhatsappClick" class="flex items-center justify-center gap-2 rounded-full bg-[#25D366] px-6 py-3 font-display
                        font-semibold
                        text-white transition hover:brightness-95">
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="currentColor">
                                <path
                                    d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.79.47 3.43 1.29 4.9L2 22l5.29-1.39c1.4.76 3 1.2 4.7 1.2h.01c5.46 0 9.91-4.45 9.91-9.91S17.5 2 12.04 2zm0 18.05h-.01c-1.6 0-3.15-.43-4.5-1.24l-.32-.19-3.13.82.84-3.05-.21-.32a8.02 8.02 0 0 1-1.24-4.26c0-4.46 3.63-8.09 8.09-8.09 4.46 0 8.09 3.63 8.09 8.09 0 4.46-3.63 8.05-8.11 8.05zm4.44-6.02c-.24-.12-1.44-.71-1.66-.79-.22-.08-.38-.12-.55.12-.16.24-.62.79-.76.95-.14.16-.28.18-.52.06-.24-.12-1.01-.37-1.92-1.18-.71-.63-1.19-1.41-1.33-1.65-.14-.24-.02-.37.1-.49.11-.11.24-.28.36-.42.12-.14.16-.24.24-.4.08-.16.04-.3-.02-.42-.06-.12-.55-1.33-.76-1.82-.2-.48-.4-.42-.55-.42-.14 0-.3-.02-.46-.02s-.42.06-.64.3c-.22.24-.85.83-.85 2.02 0 1.19.87 2.34 1 2.5.12.16 1.71 2.61 4.15 3.66.58.25 1.03.4 1.38.51.58.18 1.11.16 1.53.1.47-.07 1.44-.59 1.64-1.16.2-.57.2-1.06.14-1.16-.06-.1-.22-.16-.46-.28z" />
                            </svg>
                            {{ isOutOfStock ? 'Avísame cuando esté disponible' : 'Consultar por WhatsApp' }}
                        </a>

                        <button
                            class="flex items-center justify-center gap-2 rounded-full border border-brand-primary px-6 py-3 font-display font-semibold text-brand-primary transition hover:bg-brand-primary/5"
                            @click="addToCart">
                            <Check v-if="justAdded" class="h-5 w-5" />
                            <ShoppingCart v-else class="h-5 w-5" />
                            {{ justAdded ? 'Agregado al carrito' : 'Agregar al carrito' }}
                        </button>

                        <button
                            class="flex items-center justify-center gap-2 rounded-full border border-gray-200 px-6 py-3 font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                            @click="share">
                            <Check v-if="copied" class="h-5 w-5 text-green-600" />
                            <Share2 v-else class="h-5 w-5" />
                            {{ copied ? 'Enlace copiado' : 'Compartir' }}
                        </button>
                    </div>
                </div>
            </div>

            <section v-if="relatedProducts.length" class="mt-16">
                <h2 class="font-display text-lg font-semibold text-gray-900 dark:text-gray-100 sm:text-xl">También te
                    puede
                    interesar</h2>
                <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-4">
                    <ProductCard v-for="related in relatedProducts" :key="related.id" :product="related" />
                </div>
            </section>
        </template>
    </div>
</template>