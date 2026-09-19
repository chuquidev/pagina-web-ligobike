<script setup lang="ts">
import { computed, ref } from 'vue'
import { Package, Eye, ShoppingCart, Check } from '@lucide/vue'
import type { Product } from '@/types/catalog'
import { useSettingsStore } from '@/stores/settings'
import { useCartStore } from '@/stores/cart'
import { buildWhatsAppUrl } from '@/utils/whatsapp'
import { trackEvent } from '@/utils/analytics'
import PriceTag from './PriceTag.vue'
import AvailabilityBadge from './AvailabilityBadge.vue'
import DiscountBadge from './DiscountBadge.vue'

const props = defineProps<{ product: Product }>()
const settingsStore = useSettingsStore()
const cartStore = useCartStore()
const justAdded = ref(false)

const image = computed(() => props.product.images[0]?.thumb ?? null)

const quickWhatsappUrl = computed(() => {
    const phone = settingsStore.settings?.whatsapp_number
    if (!phone) return null
    const productUrl = `${window.location.origin}/producto/${props.product.slug}`
    return buildWhatsAppUrl(phone, `Hola, me interesa este producto: ${props.product.name}.\n${productUrl}`)
})

function trackWhatsappClick() {
    trackEvent('generate_lead', {
        currency: 'PEN',
        value: Number(props.product.sale_price ?? props.product.price),
        source: 'product_card',
        item_name: props.product.name,
    })
}

function addToCart() {
    cartStore.addItem(props.product, 1)
    justAdded.value = true
    setTimeout(() => (justAdded.value = false), 1200)
}
</script>

<template>
    <div
        class="group relative flex flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white transition-shadow hover:shadow-lg dark:border-gray-700 dark:bg-gray-900">
        <RouterLink :to="{ name: 'product', params: { slug: product.slug } }" class="absolute inset-0 z-10"
            :aria-label="`Ver ${product.name}`" />

        <div class="relative aspect-square overflow-hidden bg-gray-100 dark:bg-gray-800">
            <img v-if="image" :src="image" :alt="product.name" loading="lazy" decoding="async"
                class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105" />
            <div v-else class="flex h-full w-full items-center justify-center text-gray-300 dark:text-gray-600">
                <Package class="h-10 w-10 sm:h-12 sm:w-12" />
            </div>

            <DiscountBadge v-if="product.sale_price" :price="product.price" :sale-price="product.sale_price" />

            <div
                class="pointer-events-none absolute inset-0 flex items-center justify-center bg-black/0 opacity-0 transition-all duration-200 group-hover:bg-black/30 group-hover:opacity-100">
                <span
                    class="flex items-center gap-1.5 rounded-full bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow-lg">
                    <Eye class="h-4 w-4" />
                    Ver
                </span>
            </div>

            <button aria-label="Agregar al carrito"
                class="absolute bottom-2 left-2 z-20 flex h-9 w-9 items-center justify-center rounded-full bg-brand-primary text-white shadow-md transition-transform hover:scale-110"
                @click.stop.prevent="addToCart">
                <Check v-if="justAdded" class="h-4 w-4" />
                <ShoppingCart v-else class="h-4 w-4" />
            </button>

            <a v-if="quickWhatsappUrl" :href="quickWhatsappUrl" target="_blank" rel="noopener"
                @click.stop="trackWhatsappClick" aria-label="Consultar por WhatsApp" class="absolute bottom-2 right-2 z-20 flex h-9 w-9 items-center justify-center rounded-full bg-[#25D366]
            text-white shadow-md transition-transform hover:scale-110">
                <svg viewBox="0 0 24 24" class="h-5 w-5" fill="currentColor">
                    <path
                        d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.79.47 3.43 1.29 4.9L2 22l5.29-1.39c1.4.76 3 1.2 4.7 1.2h.01c5.46 0 9.91-4.45 9.91-9.91S17.5 2 12.04 2zm0 18.05h-.01c-1.6 0-3.15-.43-4.5-1.24l-.32-.19-3.13.82.84-3.05-.21-.32a8.02 8.02 0 0 1-1.24-4.26c0-4.46 3.63-8.09 8.09-8.09 4.46 0 8.09 3.63 8.09 8.09 0 4.46-3.63 8.05-8.11 8.05zm4.44-6.02c-.24-.12-1.44-.71-1.66-.79-.22-.08-.38-.12-.55.12-.16.24-.62.79-.76.95-.14.16-.28.18-.52.06-.24-.12-1.01-.37-1.92-1.18-.71-.63-1.19-1.41-1.33-1.65-.14-.24-.02-.37.1-.49.11-.11.24-.28.36-.42.12-.14.16-.24.24-.4.08-.16.04-.3-.02-.42-.06-.12-.55-1.33-.76-1.82-.2-.48-.4-.42-.55-.42-.14 0-.3-.02-.46-.02s-.42.06-.64.3c-.22.24-.85.83-.85 2.02 0 1.19.87 2.34 1 2.5.12.16 1.71 2.61 4.15 3.66.58.25 1.03.4 1.38.51.58.18 1.11.16 1.53.1.47-.07 1.44-.59 1.64-1.16.2-.57.2-1.06.14-1.16-.06-.1-.22-.16-.46-.28z" />
                </svg>
            </a>
        </div>

        <div class="flex flex-1 flex-col gap-2 p-3 sm:p-4">
            <span class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">
                {{ product.category.name }}<template v-if="product.brand"> · {{ product.brand.name }}</template>
            </span>
            <h3 class="font-display text-sm font-semibold text-gray-900 line-clamp-2 dark:text-gray-100 sm:text-base">{{
                product.name }}</h3>

            <div class="mt-auto pt-2">
                <PriceTag :price="product.price" :sale-price="product.sale_price" />
            </div>
            <AvailabilityBadge :availability="product.availability" class="self-start" />
        </div>
    </div>
</template>