<script setup lang="ts">
import { Sun, Moon } from '@lucide/vue'
import { useThemeStore } from '@/stores/theme'

const themeStore = useThemeStore()
</script>

<template>
    <button class="rounded-lg p-2 text-gray-500 transition hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800"
        :aria-label="themeStore.isDark ? 'Activar modo claro' : 'Activar modo oscuro'" @click="themeStore.toggle">
        <Sun v-if="themeStore.isDark" class="h-5 w-5" />
        <Moon v-else class="h-5 w-5" />
    </button>
</template>