<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { Mail, Lock, Eye, EyeOff, ShoppingBag } from '@lucide/vue'
import { useAuthStore } from '@/stores/auth'
import { useSettingsStore } from '@/stores/settings'
import ThemeToggle from '@/components/ThemeToggle.vue'
import BikePatternBackground from '@/components/BikePatternBackground.vue'

const email = ref('')
const password = ref('')
const showPassword = ref(false)
const error = ref('')
const loading = ref(false)

const authStore = useAuthStore()
const settingsStore = useSettingsStore()
const router = useRouter()
const route = useRoute()

onMounted(() => settingsStore.fetch())

async function submit() {
    if (loading.value) return
    error.value = ''
    loading.value = true
    try {
        await authStore.login(email.value, password.value)
        router.push((route.query.redirect as string) || '/admin/dashboard')
    } catch {
        error.value = 'Correo o contraseña incorrectos.'
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <div class="relative flex min-h-screen overflow-hidden bg-gray-50 dark:bg-gray-950">
        <BikePatternBackground color="var(--color-brand-primary)" :opacity="0.05" />

        <ThemeToggle class="fixed right-4 top-4 z-10" />

        <div class="relative hidden w-1/2 items-center justify-center overflow-hidden lg:flex">
            <div class="absolute inset-0"
                style="background: linear-gradient(135deg, var(--color-brand-primary), var(--color-brand-secondary))">
            </div>
            <BikePatternBackground color="white" :opacity="0.12" />

            <div class="relative flex flex-col items-center px-10 text-center text-white">
                <div class="flex h-28 w-28 items-center justify-center rounded-3xl bg-white/95 p-4 shadow-xl">
                    <img v-if="settingsStore.settings?.logo" :src="settingsStore.settings.logo"
                        :alt="settingsStore.settings.store_name" class="h-full w-full object-contain" />
                    <ShoppingBag v-else class="h-14 w-14 text-brand-primary" />
                </div>
                <h1 class="mt-6 font-display text-3xl font-bold">{{ settingsStore.settings?.store_name ?? 'Panel admin'
                    }}</h1>
                <p class="mt-3 max-w-xs text-sm text-white/80">
                    Gestiona tu catálogo, productos y pedidos desde un solo lugar.
                </p>
            </div>
        </div>

        <div class="relative z-[1] flex w-full flex-1 items-center justify-center px-4 py-12 lg:w-1/2">
            <div
                class="w-full max-w-sm rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:p-8">
                <div class="text-center lg:hidden">
                    <div
                        class="mx-auto flex h-20 w-20 items-center justify-center rounded-2xl border border-gray-200 bg-white p-3 shadow-sm dark:border-gray-800 dark:bg-gray-800">
                        <img v-if="settingsStore.settings?.logo" :src="settingsStore.settings.logo"
                            :alt="settingsStore.settings.store_name" class="h-full w-full object-contain" />
                        <ShoppingBag v-else class="h-10 w-10 text-brand-primary" />
                    </div>
                    <p class="mt-3 text-sm font-medium text-gray-500 dark:text-gray-400">{{
                        settingsStore.settings?.store_name }}</p>
                </div>

                <div class="mt-6 text-center lg:mt-0 lg:text-left">
                    <h2 class="font-display text-2xl font-bold text-gray-900 dark:text-gray-100">Panel de administración
                    </h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ingresa con tus credenciales para
                        continuar.</p>
                </div>

                <form class="mt-8 space-y-5" @submit.prevent="submit">
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Correo</label>
                        <div class="relative mt-1">
                            <Mail
                                class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                            <input v-model="email" type="email" required autocomplete="username"
                                placeholder="admin@tutienda.com"
                                class="w-full rounded-lg border border-gray-300 bg-white py-2.5 pl-10 pr-3 text-sm text-gray-900 focus:border-brand-primary focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100" />
                        </div>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Contraseña</label>
                        <div class="relative mt-1">
                            <Lock
                                class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                            <input v-model="password" :type="showPassword ? 'text' : 'password'" required
                                autocomplete="current-password" placeholder="••••••••"
                                class="w-full rounded-lg border border-gray-300 bg-white py-2.5 pl-10 pr-10 text-sm text-gray-900 focus:border-brand-primary focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100" />
                            <button type="button"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                                :aria-label="showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                                @click="showPassword = !showPassword">
                                <EyeOff v-if="showPassword" class="h-4 w-4" />
                                <Eye v-else class="h-4 w-4" />
                            </button>
                        </div>
                    </div>

                    <p v-if="error"
                        class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-600 dark:bg-red-950/40 dark:text-red-400">
                        {{ error }}</p>

                    <button type="submit" :disabled="loading"
                        class="w-full rounded-lg bg-brand-primary py-2.5 font-display font-semibold text-white transition hover:brightness-110 disabled:opacity-60">
                        {{ loading ? 'Ingresando...' : 'Ingresar' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>