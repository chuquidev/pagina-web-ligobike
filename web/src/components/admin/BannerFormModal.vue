<script setup lang="ts">
import { ref, watch } from 'vue'
import { ImagePlus } from '@lucide/vue'
import Modal from './Modal.vue'
import { adminBannersService } from '@/services/admin/banners.service'
import { useToastStore } from '@/stores/toast'
import { useFormErrors } from '@/composables/useFormErrors'
import type { Banner } from '@/types/catalog'

const props = defineProps<{ banner: Banner | null }>()
const emit = defineEmits<{ close: []; saved: [] }>()

const toastStore = useToastStore()
const { getError, parseErrors, clearErrors } = useFormErrors()

const title = ref('')
const subtitle = ref('')
const buttonText = ref('')
const buttonUrl = ref('')
const order = ref(0)
const isActive = ref(true)
const newImage = ref<File | null>(null)
const newImagePreview = ref<string | null>(null)
const saving = ref(false)

watch(
    () => props.banner,
    (banner) => {
        title.value = banner?.title ?? ''
        subtitle.value = banner?.subtitle ?? ''
        buttonText.value = banner?.button_text ?? ''
        buttonUrl.value = banner?.button_url ?? ''
        order.value = banner?.order ?? 0
        isActive.value = banner?.is_active ?? true
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
        if (title.value) formData.append('title', title.value)
        if (subtitle.value) formData.append('subtitle', subtitle.value)
        if (buttonText.value) formData.append('button_text', buttonText.value)
        if (buttonUrl.value) formData.append('button_url', buttonUrl.value)
        formData.append('order', String(order.value))
        formData.append('is_active', isActive.value ? '1' : '0')
        if (newImage.value) formData.append('image', newImage.value)

        if (props.banner) {
            await adminBannersService.update(props.banner.id, formData)
        } else {
            await adminBannersService.create(formData)
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
    <Modal :title="banner ? 'Editar banner' : 'Nuevo banner'" size="lg" @close="emit('close')">
        <form class="space-y-4" @submit.prevent="submit">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Imagen</label>
                <div class="mt-2 flex items-center gap-4">
                    <div
                        class="flex h-16 w-28 items-center justify-center overflow-hidden rounded-lg border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800">
                        <img v-if="newImagePreview ?? banner?.image" :src="newImagePreview ?? banner?.image ?? ''"
                            class="h-full w-full object-cover" />
                        <ImagePlus v-else class="h-6 w-6 text-gray-300 dark:text-gray-600" />
                    </div>
                    <label for="banner-image-input"
                        class="flex cursor-pointer items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                        <ImagePlus class="h-4 w-4" />
                        {{ banner?.image ? 'Cambiar imagen' : 'Subir imagen' }}
                    </label>
                    <input id="banner-image-input" type="file" accept="image/*" class="hidden"
                        @change="onImageSelected" />
                </div>
                <p v-if="getError('image')" class="mt-1 text-xs text-red-600 dark:text-red-400">{{ getError('image') }}
                </p>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Recomendado: imagen horizontal, mínimo
                    1600×700px.</p>
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Título</label>
                <input v-model="title" type="text" placeholder="Ej: Nueva colección de montaña"
                    class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-primary focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100" />
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Subtítulo</label>
                <input v-model="subtitle" type="text" placeholder="Ej: Hasta 20% de descuento esta semana"
                    class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-primary focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100" />
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Texto del botón</label>
                    <input v-model="buttonText" type="text" placeholder="Ver catálogo"
                        class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-primary focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100" />
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Enlace del botón</label>
                    <input v-model="buttonUrl" type="text" placeholder="/catalogo o https://..."
                        class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-primary focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100" />
                </div>
            </div>
            <p class="-mt-2 text-xs text-gray-400 dark:text-gray-500">
                Usa una ruta interna como <code>/catalogo?category=montaneras</code>, o un link completo (https://...)
                para algo externo.
            </p>

            <div>
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Orden</label>
                <input v-model.number="order" type="number" min="0"
                    class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-primary focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100" />
            </div>

            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                <input v-model="isActive" type="checkbox"
                    class="rounded border-gray-300 text-brand-primary dark:border-gray-600 dark:bg-gray-800" />
                Banner activo
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