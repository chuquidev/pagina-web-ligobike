<script setup lang="ts">
import { CheckCircle2, XCircle, Info, X } from '@lucide/vue'
import { useToastStore } from '@/stores/toast'

const toastStore = useToastStore()

const icons = { success: CheckCircle2, error: XCircle, info: Info }
const styles = {
    success: 'bg-white border-green-200 text-green-800 dark:bg-gray-900 dark:border-green-900 dark:text-green-300',
    error: 'bg-white border-red-200 text-red-800 dark:bg-gray-900 dark:border-red-900 dark:text-red-300',
    info: 'bg-white border-gray-200 text-gray-800 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-200',
}
const iconColors = { success: 'text-green-500', error: 'text-red-500', info: 'text-blue-500' }
</script>

<template>
    <Teleport to="body">
        <div
            class="pointer-events-none fixed inset-x-0 top-4 z-[200] flex flex-col items-center gap-2 px-4 sm:inset-x-auto sm:right-4 sm:items-end">
            <TransitionGroup name="toast">
                <div v-for="toast in toastStore.toasts" :key="toast.id"
                    class="pointer-events-auto flex w-full max-w-sm items-start gap-3 rounded-xl border px-4 py-3 shadow-lg"
                    :class="styles[toast.type]">
                    <component :is="icons[toast.type]" class="mt-0.5 h-5 w-5 shrink-0"
                        :class="iconColors[toast.type]" />
                    <p class="flex-1 text-sm font-medium">{{ toast.message }}</p>
                    <button class="shrink-0 text-current opacity-50 transition hover:opacity-100"
                        @click="toastStore.dismiss(toast.id)">
                        <X class="h-4 w-4" />
                    </button>
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
    transition: all 0.3s ease;
}

.toast-enter-from {
    opacity: 0;
    transform: translateY(-12px) scale(0.95);
}

.toast-leave-to {
    opacity: 0;
    transform: translateX(24px);
}

.toast-move {
    transition: transform 0.3s ease;
}
</style>