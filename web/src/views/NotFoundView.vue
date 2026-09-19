<script setup lang="ts">
import { useHead } from '@unhead/vue'
import { ShoppingBag } from '@lucide/vue'

useHead(() => ({ title: 'Página no encontrada' }))
</script>

<template>
    <div class="mx-auto flex max-w-[1400px] flex-col items-center px-4 py-24 text-center">
        <div
            class="flex h-20 w-20 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
            <ShoppingBag class="h-9 w-9" />
        </div>
        <p class="mt-6 font-display text-6xl font-bold text-gray-200 dark:text-gray-700">404</p>
        <h1 class="mt-2 font-display text-xl font-bold text-gray-900 dark:text-gray-100 sm:text-2xl">Esta página no
            existe</h1>
        <p class="mt-2 max-w-sm text-sm text-gray-500 dark:text-gray-400">
            Puede que el enlace esté roto o que el producto ya no esté disponible.
        </p>
        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <RouterLink to="/"
                class="rounded-full bg-brand-primary px-6 py-2.5 font-display text-sm font-semibold text-white transition hover:brightness-110">
                Ir al inicio
            </RouterLink>
            <RouterLink to="/catalogo"
                class="rounded-full border border-gray-200 px-6 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                Ver catálogo
            </RouterLink>
        </div>
    </div>
</template>