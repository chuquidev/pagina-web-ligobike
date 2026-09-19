<script setup lang="ts">
import { onMounted } from 'vue'
import { useSettingsStore } from '@/stores/settings'
import { useCatalogStore } from '@/stores/catalog'
import AppHeader from '@/components/AppHeader.vue'
import AppFooter from '@/components/AppFooter.vue'
import WhatsAppButton from '@/components/WhatsAppButton.vue'

const settingsStore = useSettingsStore()
const catalogStore = useCatalogStore()

onMounted(() => {
    settingsStore.fetch()
    catalogStore.fetch()
})
</script>

<template>
    <div class="flex min-h-screen flex-col">
        <AppHeader />
        <main class="flex-1">
            <RouterView />
        </main>
        <AppFooter />
        <WhatsAppButton />
    </div>
</template>