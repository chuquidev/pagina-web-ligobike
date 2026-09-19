<script setup lang="ts">
import { ref, watch } from 'vue'
import Modal from './Modal.vue'
import { adminBrandsService } from '@/services/admin/brands.service'
import { useToastStore } from '@/stores/toast'
import { useFormErrors } from '@/composables/useFormErrors'
import type { Brand } from '@/types/catalog'

const props = defineProps<{ brand: Brand | null }>()
const emit = defineEmits<{ close: []; saved: [] }>()

const toastStore = useToastStore()
const { getError, parseErrors, clearErrors } = useFormErrors()

const name = ref('')
const isActive = ref(true)
const saving = ref(false)

watch(
    () => props.brand,
    (brand) => {
        name.value = brand?.name ?? ''
        isActive.value = brand?.is_active ?? true
        clearErrors()
    },
    { immediate: true }
)

async function submit() {
    saving.value = true
    clearErrors()
    try {
        const payload = { name: name.value, is_active: isActive.value }
        if (props.brand) {
            await adminBrandsService.update(props.brand.id, payload)
        } else {
            await adminBrandsService.create(payload)
        }
        emit('saved')
    } catch (err) {
        toastStore.error(parseErrors(err))
    } finally {
        saving.value = false
    }
}
</script>

<template>
    <Modal :title="brand ? 'Editar marca' : 'Nueva marca'" @close="emit('close')">
        <form class="space-y-4" @submit.prevent="submit">
            <div>
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Nombre</label>
                <input v-model="name" type="text" required
                    class="mt-1 w-full rounded-lg border px-3 py-2 text-sm text-gray-900 focus:outline-none dark:bg-gray-800 dark:text-gray-100"
                    :class="getError('name') ? 'border-red-400 dark:border-red-700' : 'border-gray-300 focus:border-brand-primary dark:border-gray-700'" />
                <p v-if="getError('name')" class="mt-1 text-xs text-red-600 dark:text-red-400">{{ getError('name') }}
                </p>
            </div>

            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                <input v-model="isActive" type="checkbox"
                    class="rounded border-gray-300 text-brand-primary dark:border-gray-600 dark:bg-gray-800" />
                Marca activa
            </label>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button"
                    class="rounded-lg px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
                    @click="emit('close')">
                    Cancelar
                </button>
                <button type="submit" :disabled="saving"
                    class="rounded-lg bg-brand-primary px-4 py-2 text-sm font-semibold text-white hover:brightness-110 disabled:opacity-60">
                    {{ saving ? 'Guardando...' : 'Guardar' }}
                </button>
            </div>
        </form>
    </Modal>
</template>