<script setup lang="ts">
import { computed } from 'vue'
import { X, Plus, Minus, Trash2, Package } from '@lucide/vue'
import { useCartStore } from '@/stores/cart'
import { useSettingsStore } from '@/stores/settings'
import { buildWhatsAppUrl } from '@/utils/whatsapp'
import { formatCurrency } from '@/utils/currency'

const emit = defineEmits<{ close: [] }>()
const cartStore = useCartStore()
const settingsStore = useSettingsStore()

const whatsappUrl = computed(() => {
    const phone = settingsStore.settings?.whatsapp_number
    if (!phone || !cartStore.items.length) return null

    const lines = cartStore.items.map((item, i) => {
        const price = formatCurrency(item.product.sale_price ?? item.product.price)
        const url = `${window.location.origin}/producto/${item.product.slug}`
        return `${i + 1}. ${item.product.name} (x${item.quantity}) — ${price}\n${url}`
    })

    const message = [
        'Hola, me interesan estos productos:',
        '',
        ...lines,
        '',
        `Total aproximado: ${formatCurrency(cartStore.totalPrice)}`,
    ].join('\n')

    return buildWhatsAppUrl(phone, message)
})
</script>

<template>
    <Teleport to="body">
        <Transition name="backdrop-fade" appear>
            <div class="fixed inset-0 z-[90] bg-black/50 backdrop-blur-[2px]" @click="emit('close')"></div>
        </Transition>

        <Transition name="cart-panel" appear>
            <aside
                class="fixed inset-x-0 bottom-0 z-[91] flex max-h-[88vh] flex-col rounded-t-3xl bg-white shadow-2xl dark:bg-gray-900 sm:inset-y-0 sm:right-0 sm:left-auto sm:bottom-auto sm:h-full sm:max-h-none sm:w-full sm:max-w-sm sm:rounded-none">
                <div class="flex justify-center pt-2.5 sm:hidden">
                    <span class="h-1.5 w-10 rounded-full bg-gray-300 dark:bg-gray-700"></span>
                </div>

                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h2 class="font-display text-lg font-semibold text-gray-900 dark:text-gray-100">
                        Tu carrito <span v-if="cartStore.totalItems" class="text-gray-400 dark:text-gray-500">({{
                            cartStore.totalItems }})</span>
                    </h2>
                    <button
                        class="flex h-8 w-8 items-center justify-center rounded-full text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300"
                        aria-label="Cerrar" @click="emit('close')">
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto px-5 py-4">
                    <div v-if="!cartStore.items.length"
                        class="flex h-full flex-col items-center justify-center px-4 text-center">
                        <div
                            class="flex h-16 w-16 items-center justify-center rounded-full bg-brand-primary/10 text-brand-primary">
                            <Package class="h-7 w-7" />
                        </div>
                        <p class="mt-4 font-display text-base font-semibold text-gray-900 dark:text-gray-100">Tu carrito
                            está vacío</p>
                        <p class="mt-1 text-sm text-gray-400 dark:text-gray-500">Agrega productos desde el catálogo para
                            armar tu pedido.</p>
                        <RouterLink to="/catalogo"
                            class="mt-5 rounded-full bg-brand-primary px-5 py-2.5 text-sm font-semibold text-white transition hover:brightness-110"
                            @click="emit('close')">
                            Ver catálogo
                        </RouterLink>
                    </div>

                    <TransitionGroup v-else name="cart-item" tag="div" class="relative flex flex-col gap-4">
                        <div v-for="item in cartStore.items" :key="item.product.id" class="flex gap-3">
                            <div class="h-16 w-16 shrink-0 overflow-hidden rounded-xl bg-gray-100 dark:bg-gray-800">
                                <img v-if="item.product.images[0]" :src="item.product.images[0].thumb"
                                    class="h-full w-full object-cover" />
                                <div v-else
                                    class="flex h-full w-full items-center justify-center text-gray-300 dark:text-gray-600">
                                    <Package class="h-6 w-6" />
                                </div>
                            </div>

                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-gray-900 dark:text-gray-100">{{
                                    item.product.name }}</p>
                                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                                    {{ formatCurrency(item.product.sale_price ?? item.product.price) }}
                                </p>

                                <div class="mt-2 flex items-center gap-2">
                                    <button
                                        class="flex h-7 w-7 items-center justify-center rounded-full border border-gray-200 text-gray-500 transition hover:scale-105 hover:bg-gray-50 active:scale-95 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-800"
                                        @click="cartStore.updateQuantity(item.product.id, item.quantity - 1)">
                                        <Minus class="h-3.5 w-3.5" />
                                    </button>
                                    <span
                                        class="w-6 text-center text-sm font-medium text-gray-900 dark:text-gray-100">{{
                                            item.quantity }}</span>
                                    <button
                                        class="flex h-7 w-7 items-center justify-center rounded-full border border-gray-200 text-gray-500 transition hover:scale-105 hover:bg-gray-50 active:scale-95 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-800"
                                        @click="cartStore.updateQuantity(item.product.id, item.quantity + 1)">
                                        <Plus class="h-3.5 w-3.5" />
                                    </button>
                                    <button class="ml-auto text-gray-400 transition hover:scale-110 hover:text-red-500"
                                        aria-label="Quitar" @click="cartStore.removeItem(item.product.id)">
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </TransitionGroup>
                </div>

                <div v-if="cartStore.items.length" class="border-t border-gray-100 px-5 pt-4 dark:border-gray-800"
                    style="padding-bottom: max(1rem, env(safe-area-inset-bottom))">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400">Total aproximado</span>
                        <span class="font-display text-lg font-semibold text-gray-900 dark:text-gray-100">{{
                            formatCurrency(cartStore.totalPrice) }}</span>
                    </div>

                    <a v-if="whatsappUrl" :href="whatsappUrl" target="_blank" rel="noopener" class="mt-3 flex items-center justify-center gap-2 rounded-full bg-[#25D366] px-6 py-3.5
                    font-display font-semibold text-white shadow-lg shadow-[#25D366]/20 transition hover:scale-[1.02]
                    hover:brightness-95 active:scale-[0.98]">
                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="currentColor">
                            <path
                                d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.79.47 3.43 1.29 4.9L2 22l5.29-1.39c1.4.76 3 1.2 4.7 1.2h.01c5.46 0 9.91-4.45 9.91-9.91S17.5 2 12.04 2zm0 18.05h-.01c-1.6 0-3.15-.43-4.5-1.24l-.32-.19-3.13.82.84-3.05-.21-.32a8.02 8.02 0 0 1-1.24-4.26c0-4.46 3.63-8.09 8.09-8.09 4.46 0 8.09 3.63 8.09 8.09 0 4.46-3.63 8.05-8.11 8.05zm4.44-6.02c-.24-.12-1.44-.71-1.66-.79-.22-.08-.38-.12-.55.12-.16.24-.62.79-.76.95-.14.16-.28.18-.52.06-.24-.12-1.01-.37-1.92-1.18-.71-.63-1.19-1.41-1.33-1.65-.14-.24-.02-.37.1-.49.11-.11.24-.28.36-.42.12-.14.16-.24.24-.4.08-.16.04-.3-.02-.42-.06-.12-.55-1.33-.76-1.82-.2-.48-.4-.42-.55-.42-.14 0-.3-.02-.46-.02s-.42.06-.64.3c-.22.24-.85.83-.85 2.02 0 1.19.87 2.34 1 2.5.12.16 1.71 2.61 4.15 3.66.58.25 1.03.4 1.38.51.58.18 1.11.16 1.53.1.47-.07 1.44-.59 1.64-1.16.2-.57.2-1.06.14-1.16-.06-.1-.22-.16-.46-.28z" />
                        </svg>
                        Consultar por WhatsApp
                    </a>

                    <button
                        class="mt-3 w-full text-center text-xs text-gray-400 transition hover:text-red-500 dark:text-gray-500"
                        @click="cartStore.clear()">
                        Vaciar carrito
                    </button>
                </div>
            </aside>
        </Transition>
    </Teleport>
</template>

<style scoped>
.backdrop-fade-enter-active,
.backdrop-fade-leave-active {
    transition: opacity 0.25s ease;
}

.backdrop-fade-enter-from,
.backdrop-fade-leave-to {
    opacity: 0;
}

.cart-panel-enter-active,
.cart-panel-leave-active {
    transition: transform 0.32s cubic-bezier(0.32, 0.72, 0, 1);
}

.cart-panel-enter-from,
.cart-panel-leave-to {
    transform: translateY(100%);
}

@media (min-width: 640px) {

    .cart-panel-enter-from,
    .cart-panel-leave-to {
        transform: translateX(100%);
    }
}

.cart-item-enter-active,
.cart-item-leave-active {
    transition: all 0.25s ease;
}

.cart-item-enter-from {
    opacity: 0;
    transform: translateX(16px);
}

.cart-item-leave-to {
    opacity: 0;
    transform: translateX(-16px);
}

.cart-item-leave-active {
    position: absolute;
    width: 100%;
}

.cart-item-move {
    transition: transform 0.25s ease;
}
</style>