<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue'
import { X } from '@lucide/vue'

withDefaults(defineProps<{ title: string; size?: 'md' | 'lg' }>(), { size: 'md' })
const emit = defineEmits<{ close: [] }>()

const modalRef = ref<HTMLElement | null>(null)
let previouslyFocused: HTMLElement | null = null

function getFocusable(): HTMLElement[] {
    if (!modalRef.value) return []
    return Array.from(
        modalRef.value.querySelectorAll<HTMLElement>(
            'a[href], button:not([disabled]), textarea:not([disabled]), input:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])'
        )
    )
}

function onKeydown(e: KeyboardEvent) {
    if (e.key === 'Escape') {
        emit('close')
        return
    }
    if (e.key !== 'Tab') return

    const focusable = getFocusable()
    if (!focusable.length) return
    const first = focusable[0]
    const last = focusable[focusable.length - 1]

    if (e.shiftKey && document.activeElement === first) {
        e.preventDefault()
        last.focus()
    } else if (!e.shiftKey && document.activeElement === last) {
        e.preventDefault()
        first.focus()
    }
}

onMounted(() => {
    previouslyFocused = document.activeElement as HTMLElement
    window.addEventListener('keydown', onKeydown)
    requestAnimationFrame(() => getFocusable()[0]?.focus())
})

onUnmounted(() => {
    window.removeEventListener('keydown', onKeydown)
    previouslyFocused?.focus()
})
</script>

<template>
    <Teleport to="body">
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="emit('close')">
            <div ref="modalRef" role="dialog" aria-modal="true" :aria-label="title"
                class="max-h-[90vh] w-full overflow-y-auto rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900"
                :class="size === 'lg' ? 'max-w-2xl' : 'max-w-md'">
                <div class="flex items-center justify-between">
                    <h2 class="font-display text-lg font-semibold text-gray-900 dark:text-gray-100">{{ title }}</h2>
                    <button class="text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300"
                        @click="emit('close')">
                        <X class="h-5 w-5" />
                    </button>
                </div>
                <div class="mt-4">
                    <slot />
                </div>
            </div>
        </div>
    </Teleport>
</template>