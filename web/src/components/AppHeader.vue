<script setup lang="ts">
import { ref, watch } from 'vue'
import { ShoppingBag, ShoppingCart, Menu, X, Search } from '@lucide/vue'
import { useSettingsStore } from '@/stores/settings'
import { useCartStore } from '@/stores/cart'
import ThemeToggle from '@/components/ThemeToggle.vue'
import CartDrawer from '@/components/CartDrawer.vue'
import AppSearchBox from '@/components/AppSearchBox.vue'

const settingsStore = useSettingsStore()
const cartStore = useCartStore()
const mobileMenuOpen = ref(false)
const mobileSearchOpen = ref(false)
const cartOpen = ref(false)
const bump = ref(false)

watch(
    () => cartStore.totalItems,
    (newVal, oldVal) => {
        if (newVal > oldVal) {
            bump.value = true
            setTimeout(() => (bump.value = false), 350)
        }
    }
)
</script>

<template>
    <header
        class="sticky top-0 z-40 border-b border-gray-200 bg-white/90 backdrop-blur dark:border-gray-800 dark:bg-gray-950/90">
        <div class="mx-auto flex max-w-[1400px] items-center gap-3 px-4 py-3 lg:gap-4">
            <RouterLink to="/" class="flex min-w-0 shrink-0 items-center gap-2.5 font-bold text-brand-primary">
                <img v-if="settingsStore.settings?.logo" :src="settingsStore.settings.logo"
                    :alt="settingsStore.settings.store_name" class="h-11 w-11 shrink-0 rounded-lg object-contain" />
                <ShoppingBag v-else class="h-9 w-9 shrink-0" />
                <span class="hidden truncate text-base sm:inline">{{ settingsStore.settings?.store_name ??
                    'Cargando...' }}</span>
            </RouterLink>

            <!-- Nav de escritorio: recién a partir de lg (1024px) — antes de eso queda apretado
                 junto al buscador, así que tablets usan el menú hamburguesa, más cómodo al tacto. -->
            <nav class="hidden shrink-0 items-center gap-1 lg:ml-3 lg:flex">
                <RouterLink to="/" active-class=""
                    exact-active-class="!bg-brand-primary/10 !text-brand-primary dark:!bg-brand-primary/15"
                    class="whitespace-nowrap rounded-full px-3.5 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white">
                    Inicio</RouterLink>
                <RouterLink to="/catalogo"
                    active-class="!bg-brand-primary/10 !text-brand-primary dark:!bg-brand-primary/15"
                    class="whitespace-nowrap rounded-full px-3.5 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white">
                    Catálogo</RouterLink>
                <RouterLink to="/nosotros"
                    active-class="!bg-brand-primary/10 !text-brand-primary dark:!bg-brand-primary/15"
                    class="whitespace-nowrap rounded-full px-3.5 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white">
                    Nosotros</RouterLink>
                <RouterLink to="/reservar-mantenimiento"
                    active-class="!bg-brand-primary/10 !text-brand-primary dark:!bg-brand-primary/15"
                    class="whitespace-nowrap rounded-full px-3.5 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white">
                    Mantenimiento</RouterLink>
            </nav>

            <AppSearchBox class="ml-auto hidden max-w-md flex-1 lg:block" />

            <button
                class="relative hidden shrink-0 rounded-lg p-2 text-gray-600 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800 lg:block"
                aria-label="Ver carrito" @click="cartOpen = true">
                <ShoppingCart class="h-5 w-5 transition-transform duration-300" :class="{ 'scale-125': bump }" />
                <span v-if="cartStore.totalItems"
                    class="absolute -right-1 -top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-brand-primary px-1 text-[10px] font-bold text-white transition-transform duration-300"
                    :class="{ 'scale-125': bump }">
                    {{ cartStore.totalItems }}
                </span>
            </button>

            <ThemeToggle class="hidden shrink-0 lg:block" />

            <div class="ml-auto flex shrink-0 items-center gap-1 lg:hidden">
                <button
                    class="relative rounded-lg p-2 text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
                    aria-label="Ver carrito" @click="cartOpen = true">
                    <ShoppingCart class="h-5 w-5 transition-transform duration-300" :class="{ 'scale-125': bump }" />
                    <span v-if="cartStore.totalItems"
                        class="absolute -right-1 -top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-brand-primary px-1 text-[10px] font-bold text-white transition-transform duration-300"
                        :class="{ 'scale-125': bump }">
                        {{ cartStore.totalItems }}
                    </span>
                </button>
                <ThemeToggle />
                <button class="rounded-lg p-2 text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
                    aria-label="Buscar" @click="mobileSearchOpen = !mobileSearchOpen; mobileMenuOpen = false">
                    <Search class="h-5 w-5" />
                </button>
                <button class="rounded-lg p-2 text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
                    aria-label="Abrir menú" @click="mobileMenuOpen = !mobileMenuOpen; mobileSearchOpen = false">
                    <component :is="mobileMenuOpen ? X : Menu" class="h-5 w-5" />
                </button>
            </div>
        </div>

        <div v-if="mobileSearchOpen" class="border-t border-gray-100 px-4 py-3 dark:border-gray-800 lg:hidden">
            <AppSearchBox autofocus @navigated="mobileSearchOpen = false" />
        </div>

        <nav v-if="mobileMenuOpen"
            class="absolute inset-x-0 top-full flex flex-col border-t border-gray-100 bg-white px-4 py-2 text-sm font-medium text-gray-600 shadow-lg dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 lg:hidden">
            <RouterLink to="/" active-class=""
                exact-active-class="!bg-brand-primary/10 !text-brand-primary dark:!bg-brand-primary/15"
                class="rounded-lg px-3 py-2.5 hover:bg-gray-50 hover:text-brand-primary dark:hover:bg-gray-800"
                @click="mobileMenuOpen = false">Inicio</RouterLink>
            <RouterLink to="/catalogo" active-class="!bg-brand-primary/10 !text-brand-primary dark:!bg-brand-primary/15"
                class="rounded-lg px-3 py-2.5 hover:bg-gray-50 hover:text-brand-primary dark:hover:bg-gray-800"
                @click="mobileMenuOpen = false">Catálogo</RouterLink>
            <RouterLink to="/nosotros" active-class="!bg-brand-primary/10 !text-brand-primary dark:!bg-brand-primary/15"
                class="rounded-lg px-3 py-2.5 hover:bg-gray-50 hover:text-brand-primary dark:hover:bg-gray-800"
                @click="mobileMenuOpen = false">Nosotros</RouterLink>
            <RouterLink to="/reservar-mantenimiento"
                active-class="!bg-brand-primary/10 !text-brand-primary dark:!bg-brand-primary/15"
                class="rounded-lg px-3 py-2.5 hover:bg-gray-50 hover:text-brand-primary dark:hover:bg-gray-800"
                @click="mobileMenuOpen = false">Mantenimiento</RouterLink>
        </nav>

        <CartDrawer v-if="cartOpen" @close="cartOpen = false" />
    </header>
</template>