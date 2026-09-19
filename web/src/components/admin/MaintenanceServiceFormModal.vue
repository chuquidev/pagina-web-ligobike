<script setup lang="ts">
import { ref, watch } from 'vue'
import Modal from './Modal.vue'
import { adminMaintenanceServicesService } from '@/services/admin/maintenance.service'
import { useToastStore } from '@/stores/toast'
import { useFormErrors } from '@/composables/useFormErrors'
import type { MaintenanceService } from '@/types/catalog'

const props = defineProps<{ service: MaintenanceService | null }>()
const emit = defineEmits<{ close: []; saved: [] }>()

const toastStore = useToastStore()
const { getError, parseErrors, clearErrors } = useFormErrors()

const name = ref('')
const description = ref('')
const durationMinutes = ref(30)
const price = ref('')
const isActive = ref(true)
const order = ref(0)
const saving = ref(false)

watch(
    () => props.service,
    (service) => {
        name.value = service?.name ?? ''
        description.value = service?.description ?? ''
        durationMinutes.value = service?.duration_minutes ?? 30
        price.value = service?.price ?? ''
        isActive.value = service?.is_active ?? true
        order.value = service?.order ?? 0
        clearErrors()
    },
    { immediate: true }
)

async function submit() {
    saving.value = true
    clearErrors()
    try {
        const payload = {
            name: name.value,
            description: description.value || undefined,
            duration_minutes: durationMinutes.value,
            price: price.value || null,
            is_active: isActive.value,
            order: order.value,
        }
        if (props.service) {
            await adminMaintenanceServicesService.update(props.service.id, payload)
        } else {
            await adminMaintenanceServicesService.create(payload)
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
    <Modal :title="service ? 'Editar servicio' : 'Nuevo servicio'" @close="emit('close')">
        <form class="space-y-4" @submit.prevent="submit">
            <div>
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Nombre</label>
                <input v-model="name" type="text" required placeholder="Ej: Mantenimiento básico"
                    class="mt-1 w-full rounded-lg border px-3 py-2 text-sm text-gray-900 focus:outline-none dark:bg-gray-800 dark:text-gray-100"
                    :class="getError('name') ? 'border-red-400 dark:border-red-700' : 'border-gray-300 focus:border-brand-primary dark:border-gray-700'" />
                <p v-if="getError('name')" class="mt-1 text-xs text-red-600 dark:text-red-400">{{ getError('name') }}
                </p>
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Descripción</label>
                <textarea v-model="description" rows="3"
                    class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-primary focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"></textarea>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Duración (minutos)</label>
                    <input v-model.number="durationMinutes" type="number" min="5" step="5" required
                        class="mt-1 w-full rounded-lg border px-3 py-2 text-sm text-gray-900 focus:outline-none dark:bg-gray-800 dark:text-gray-100"
                        :class="getError('duration_minutes') ? 'border-red-400 dark:border-red-700' : 'border-gray-300 focus:border-brand-primary dark:border-gray-700'" />
                    <p v-if="getError('duration_minutes')" class="mt-1 text-xs text-red-600 dark:text-red-400">{{
                        getError('duration_minutes') }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Precio referencial
                        (opcional)</label>
                    <input v-model="price" type="number" step="0.01" min="0" placeholder="50.00"
                        class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-primary focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100" />
                </div>
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Orden</label>
                <input v-model.number="order" type="number" min="0"
                    class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-primary focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100" />
            </div>

            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                <input v-model="isActive" type="checkbox"
                    class="rounded border-gray-300 text-brand-primary dark:border-gray-600 dark:bg-gray-800" />
                Servicio activo
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