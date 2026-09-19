<script setup lang="ts">
import Modal from './Modal.vue'

withDefaults(
    defineProps<{
        title: string
        message: string
        confirmLabel?: string
        variant?: 'danger' | 'primary'
    }>(),
    { confirmLabel: 'Eliminar', variant: 'danger' }
)
const emit = defineEmits<{ confirm: []; cancel: [] }>()
</script>

<template>
    <Modal :title="title" @close="emit('cancel')">
        <p class="text-sm text-gray-600 dark:text-gray-400">{{ message }}</p>
        <div class="mt-6 flex justify-end gap-2">
            <button type="button"
                class="rounded-lg px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
                @click="emit('cancel')">
                Cancelar
            </button>
            <button type="button" class="rounded-lg px-4 py-2 text-sm font-semibold text-white transition"
                :class="variant === 'danger' ? 'bg-red-600 hover:bg-red-700' : 'bg-brand-primary hover:brightness-110'"
                @click="emit('confirm')">
                {{ confirmLabel }}
            </button>
        </div>
    </Modal>
</template>