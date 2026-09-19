<script setup lang="ts">
import { ref, watch } from 'vue'
import { ImagePlus } from '@lucide/vue'
import Modal from './Modal.vue'
import { adminCategoriesService } from '@/services/admin/categories.service'
import { useToastStore } from '@/stores/toast'
import { useFormErrors } from '@/composables/useFormErrors'
import type { Category } from '@/types/catalog'

const props = defineProps<{ category: Category | null }>()
const emit = defineEmits<{ close: []; saved: [] }>()

const toastStore = useToastStore()
const { getError, parseErrors, clearErrors } = useFormErrors()

const name = ref('')
const order = ref(0)
const isActive = ref(true)
const newImage = ref<File | null>(null)
const newImagePreview = ref<string | null>(null)
const saving = ref(false)

watch(
    () => props.category,
    (category) => {
        name.value = category?.name ?? ''
        order.value = category?.order ?? 0
        isActive.value = category?.is_active ?? true
        newImage.value = null
        newImagePreview.value = null
        clearErrors()
    },
    { immediate: true }
)

function onImageSelected(event: Event) {
    const file = (event.target as HTMLInputElement).files?.[0]
    if (!file) return
    newImage.value = file
    newImagePreview.value = URL.createObjectURL(file)
}

async function submit() {
    saving.value = true
    clearErrors()
    try {
        const formData = new FormData()
        formData.append('name', name.value)
        formData.append('order', String(order.value))
        formData.append('is_active', isActive.value ? '1' : '0')
        if (newImage.value) formData.append('image', newImage.value)

        if (props.category) {
            await adminCategoriesService.update(props.category.id, formData)
        } else {
            await adminCategoriesService.create(formData)
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
    <Modal :title="category ? 'Editar categoría' : 'Nueva categoría'" @close="emit('close')">
        <form class="space-y-4" @submit.prevent="submit">
            <div>
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Nombre</label>
                <input v-model="name" type="text" required
                    class="mt-1 w-full rounded-lg border px-3 py-2 text-sm text-gray-900 focus:outline-none dark:bg-gray-800 dark:text-gray-100"
                    :class="getError('name') ? 'border-red-400 dark:border-red-700' : 'border-gray-300 focus:border-brand-primary dark:border-gray-700'" />
                <p v-if="getError('name')" class="mt-1 text-xs text-red-600 dark:text-red-400">{{ getError('name') }}
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Imagen</label>
                <div class="mt-2 flex items-center gap-4">
                    <div
                        class="flex h-16 w-16 items-center justify-center overflow-hidden rounded-lg border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800">
                        <img v-if="newImagePreview ?? category?.image" :src="newImagePreview ?? category?.image ?? ''"
                            class="h-full w-full object-cover" />
                        <ImagePlus v-else class="h-6 w-6 text-gray-300 dark:text-gray-600" />
                    </div>
                    <label for="category-image-input"
                        class="flex cursor-pointer items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                        <ImagePlus class="h-4 w-4" />
                        {{ category?.image ? 'Cambiar imagen' : 'Subir imagen' }}
                    </label>
                    <input id="category-image-input" type="file" accept="image/*" class="hidden"
                        @change="onImageSelected" />
                </div>
                <p v-if="getError('image')" class="mt-1 text-xs text-red-600 dark:text-red-400">{{ getError('image') }}
                </p>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Se muestra en la sección "Categorías" del
                    inicio.</p>
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Orden</label>
                <input v-model.number="order" type="number" min="0"
                    class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-primary focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100" />
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Menor número = aparece primero.</p>
            </div>

            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                <input v-model="isActive" type="checkbox"
                    class="rounded border-gray-300 text-brand-primary dark:border-gray-600 dark:bg-gray-800" />
                Categoría activa
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