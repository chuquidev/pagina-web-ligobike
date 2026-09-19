<script setup lang="ts">
import { computed } from 'vue'
import { useSettingsStore } from '@/stores/settings'

const settingsStore = useSettingsStore()

const whatsappUrl = computed(() => {
    const phone = settingsStore.settings?.whatsapp_number?.replace(/\D/g, '')
    return phone ? `https://wa.me/${phone}` : null
})
</script>

<template>
    <a v-if="whatsappUrl" :href="whatsappUrl" target="_blank" rel="noopener" aria-label="Escríbenos por WhatsApp" class="group fixed bottom-6 right-6 z-50 flex h-15 w-15 items-center justify-center rounded-full bg-[#25D366]
    text-white shadow-lg shadow-black/20 transition-transform duration-200 hover:scale-105">
        <span class="absolute inset-0 -z-10 animate-ping rounded-full bg-[#25D366] opacity-75"></span>

        <svg viewBox="0 0 24 24" class="h-8 w-8" fill="currentColor">
            <path
                d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.79.47 3.43 1.29 4.9L2 22l5.29-1.39c1.4.76 3 1.2 4.7 1.2h.01c5.46 0 9.91-4.45 9.91-9.91S17.5 2 12.04 2zm0 18.05h-.01c-1.6 0-3.15-.43-4.5-1.24l-.32-.19-3.13.82.84-3.05-.21-.32a8.02 8.02 0 0 1-1.24-4.26c0-4.46 3.63-8.09 8.09-8.09 4.46 0 8.09 3.63 8.09 8.09 0 4.46-3.63 8.05-8.11 8.05zm4.44-6.02c-.24-.12-1.44-.71-1.66-.79-.22-.08-.38-.12-.55.12-.16.24-.62.79-.76.95-.14.16-.28.18-.52.06-.24-.12-1.01-.37-1.92-1.18-.71-.63-1.19-1.41-1.33-1.65-.14-.24-.02-.37.1-.49.11-.11.24-.28.36-.42.12-.14.16-.24.24-.4.08-.16.04-.3-.02-.42-.06-.12-.55-1.33-.76-1.82-.2-.48-.4-.42-.55-.42-.14 0-.3-.02-.46-.02s-.42.06-.64.3c-.22.24-.85.83-.85 2.02 0 1.19.87 2.34 1 2.5.12.16 1.71 2.61 4.15 3.66.58.25 1.03.4 1.38.51.58.18 1.11.16 1.53.1.47-.07 1.44-.59 1.64-1.16.2-.57.2-1.06.14-1.16-.06-.1-.22-.16-.46-.28z" />
        </svg>

        <span
            class="pointer-events-none absolute right-full mr-3 whitespace-nowrap rounded-lg bg-gray-900 px-3 py-1.5 text-sm font-medium text-white opacity-0 shadow-md transition-opacity duration-200 group-hover:opacity-100">
            ¿Necesitas ayuda?
        </span>
    </a>
</template>