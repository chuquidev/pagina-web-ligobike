<script setup lang="ts">
import { computed } from 'vue'
import { MapPin, Clock, Mail, Phone } from '@lucide/vue'
import { useSettingsStore } from '@/stores/settings'

const settingsStore = useSettingsStore()

const scheduleLines = computed(() => {
    const raw = settingsStore.settings?.schedule
    if (!raw) return []
    return raw.split('.').map((s) => s.trim()).filter(Boolean)
})

const whatsappHref = computed(() => {
    const phone = settingsStore.settings?.whatsapp_number?.replace(/\D/g, '')
    return phone ? `https://wa.me/${phone}` : null
})

const currentYear = new Date().getFullYear()
</script>

<template>
    <footer class="border-t border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-gray-900">
        <div class="mx-auto max-w-[1400px] px-4 py-10 sm:py-12">
            <div class="grid gap-8 sm:grid-cols-2 sm:gap-10 lg:grid-cols-5">
                <div>
                    <div class="flex items-center gap-2">
                        <img v-if="settingsStore.settings?.logo" :src="settingsStore.settings.logo"
                            :alt="settingsStore.settings.store_name" class="h-12 w-12 rounded object-contain" />
                        <span class="font-display font-semibold text-gray-900 dark:text-gray-100">{{
                            settingsStore.settings?.store_name }}</span>
                    </div>
                    <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">Más que bicicletas, estilo de vida — consulta y compra directo por WhatsApp..</p>
                </div>

                <div>
                    <h3 class="font-display text-sm font-semibold text-gray-900 dark:text-gray-100">Contacto</h3>
                    <ul class="mt-3 space-y-2.5 text-sm text-gray-600 dark:text-gray-400">
                        <li v-if="settingsStore.settings?.address" class="flex items-start gap-2">
                            <MapPin class="mt-0.5 h-4 w-4 shrink-0 text-gray-400" />
                            <span>{{ settingsStore.settings.address }}</span>
                        </li>
                        <li v-if="whatsappHref">
                            <a :href="whatsappHref" target="_blank" rel="noopener"
                                class="flex items-center gap-2 hover:text-brand-primary">
                                <Phone class="h-4 w-4 shrink-0 text-gray-400" />
                                {{ settingsStore.settings?.whatsapp_number }}
                            </a>
                        </li>
                        <li v-if="settingsStore.settings?.email">
                            <a :href="`mailto:${settingsStore.settings.email}`"
                                class="flex items-center gap-2 hover:text-brand-primary">
                                <Mail class="h-4 w-4 shrink-0 text-gray-400" />
                                {{ settingsStore.settings.email }}
                            </a>
                        </li>
                    </ul>
                </div>

                <div>
                    <h3 class="font-display text-sm font-semibold text-gray-900 dark:text-gray-100">Horario</h3>
                    <ul v-if="scheduleLines.length" class="mt-3 space-y-2 text-sm text-gray-600 dark:text-gray-400">
                        <li v-for="(line, i) in scheduleLines" :key="i" class="flex items-start gap-2">
                            <Clock class="mt-0.5 h-4 w-4 shrink-0 text-gray-400" />
                            <span>{{ line }}</span>
                        </li>
                    </ul>
                    <p v-else class="mt-3 text-sm text-gray-400 dark:text-gray-500">No especificado.</p>
                </div>

                <div>
                    <h3 class="font-display text-sm font-semibold text-gray-900 dark:text-gray-100">Ayuda</h3>
                    <ul class="mt-3 space-y-2.5 text-sm text-gray-600 dark:text-gray-400">
                        <li>
                            <RouterLink to="/nosotros" class="hover:text-brand-primary">Nosotros</RouterLink>
                        </li>
                        <li>
                            <RouterLink to="/preguntas-frecuentes" class="hover:text-brand-primary">Preguntas frecuentes
                            </RouterLink>
                        </li>
                        <li>
                            <RouterLink to="/guia-tallas" class="hover:text-brand-primary">Guía de tallas</RouterLink>
                        </li>
                    </ul>
                </div>

                <div>
                    <h3 class="font-display text-sm font-semibold text-gray-900 dark:text-gray-100">Síguenos</h3>
                    <ul class="mt-3 space-y-2.5 text-sm text-gray-600 dark:text-gray-400">
                        <li v-if="settingsStore.settings?.facebook_url">
                            <a :href="settingsStore.settings.facebook_url" target="_blank" rel="noopener"
                                class="flex items-center gap-2 hover:text-brand-primary">
                                <svg viewBox="0 0 24 24" class="h-4 w-4 shrink-0" fill="currentColor">
                                    <path
                                        d="M22 12a10 10 0 1 0-11.56 9.88v-6.99H7.9V12h2.54V9.8c0-2.5 1.5-3.89 3.79-3.89 1.1 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56V12h2.78l-.45 2.89h-2.33v6.99A10 10 0 0 0 22 12z" />
                                </svg>
                                Facebook
                            </a>
                        </li>
                        <li v-if="settingsStore.settings?.instagram_url">
                            <a :href="settingsStore.settings.instagram_url" target="_blank" rel="noopener"
                                class="flex items-center gap-2 hover:text-brand-primary">
                                <svg viewBox="0 0 24 24" class="h-4 w-4 shrink-0" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <rect width="18" height="18" x="3" y="3" rx="5" ry="5" />
                                    <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z" />
                                    <line x1="17.5" x2="17.51" y1="6.5" y2="6.5" />
                                </svg>
                                Instagram
                            </a>
                        </li>
                        <li v-if="settingsStore.settings?.tiktok_url">
                            <a :href="settingsStore.settings.tiktok_url" target="_blank" rel="noopener"
                                class="flex items-center gap-2 hover:text-brand-primary">
                                <svg viewBox="0 0 24 24" class="h-4 w-4 shrink-0" fill="currentColor">
                                    <path
                                        d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z" />
                                </svg>
                                TikTok
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <div
                class="mt-8 flex flex-col gap-3 border-t border-gray-200 pt-6 text-xs text-gray-400 dark:border-gray-800 dark:text-gray-500 sm:mt-10 sm:flex-row sm:items-center sm:justify-between">
                <p>&copy; {{ currentYear }} {{ settingsStore.settings?.store_name }}. Todos los derechos reservados.</p>
                <div class="flex gap-4">
                    <RouterLink to="/politica-privacidad" class="hover:text-gray-600 dark:hover:text-gray-300">Política
                        de privacidad</RouterLink>
                    <RouterLink to="/terminos-condiciones" class="hover:text-gray-600 dark:hover:text-gray-300">Términos
                        y condiciones</RouterLink>
                </div>
            </div>
        </div>
    </footer>
</template>