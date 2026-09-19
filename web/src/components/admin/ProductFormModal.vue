<script setup lang="ts">
import { ref, watch } from 'vue'
import { Plus, X as XIcon, Trash2, ImagePlus } from '@lucide/vue'
import Modal from './Modal.vue'
import ConfirmDialog from './ConfirmDialog.vue'
import { adminProductsService } from '@/services/admin/products.service'
import { useToastStore } from '@/stores/toast'
import { useFormErrors } from '@/composables/useFormErrors'
import type { Product, Category, Brand, Availability, ProductImage } from '@/types/catalog'

const props = defineProps<{ product: Product | null; categories: Category[]; brands: Brand[] }>()
const emit = defineEmits<{ close: []; saved: [] }>()

const toastStore = useToastStore()
const { getError, parseErrors, clearErrors } = useFormErrors()

const name = ref('')
const sku = ref('')
const categoryId = ref<number | null>(null)
const brandId = ref<number | null>(null)
const description = ref('')
const price = ref('')
const salePrice = ref('')
const stock = ref('')
const minPrice = ref('')
const availability = ref<Availability>('in_stock')
const isFeatured = ref(false)
const isActive = ref(true)
const features = ref<string[]>([])
const newFeature = ref('')
const existingImages = ref<ProductImage[]>([])
const newImages = ref<File[]>([])
const newImagePreviews = ref<string[]>([])
const saving = ref(false)
const confirmDeleteImageId = ref<number | null>(null)

watch(
    () => props.product,
    (product) => {
        name.value = product?.name ?? ''
        sku.value = product?.sku ?? ''
        categoryId.value = product?.category.id ?? props.categories[0]?.id ?? null
        brandId.value = product?.brand?.id ?? null
        description.value = product?.description ?? ''
        price.value = product?.price ?? ''
        salePrice.value = product?.sale_price ?? ''
        stock.value = product?.stock !== null && product?.stock !== undefined ? String(product.stock) : ''
        minPrice.value = product?.min_price ?? ''
        availability.value = product?.availability ?? 'in_stock'
        isFeatured.value = product?.is_featured ?? false
        isActive.value = product?.is_active ?? true
        features.value = product?.features ? [...product.features] : []
        existingImages.value = product?.images ? [...product.images] : []
        newImages.value = []
        newImagePreviews.value = []
        clearErrors()
    },
    { immediate: true }
)

function addFeature() {
    const value = newFeature.value.trim()
    if (!value) return
    features.value.push(value)
    newFeature.value = ''
}

function removeFeature(index: number) {
    features.value.splice(index, 1)
}

function onFilesSelected(event: Event) {
    const files = Array.from((event.target as HTMLInputElement).files ?? [])
    newImages.value.push(...files)
    newImagePreviews.value.push(...files.map((f) => URL.createObjectURL(f)))
}

function removeNewImage(index: number) {
    newImages.value.splice(index, 1)
    newImagePreviews.value.splice(index, 1)
}

function requestRemoveExistingImage(mediaId: number) {
    confirmDeleteImageId.value = mediaId
}

async function confirmRemoveExistingImage() {
    if (!props.product || confirmDeleteImageId.value === null) return
    await adminProductsService.deleteImage(props.product.id, confirmDeleteImageId.value)
    existingImages.value = existingImages.value.filter((img) => img.id !== confirmDeleteImageId.value)
    confirmDeleteImageId.value = null
    toastStore.success('Imagen eliminada.')
}

function buildFormData(): FormData {
    const formData = new FormData()
    formData.append('name', name.value)
    if (sku.value.trim()) formData.append('sku', sku.value.trim())
    formData.append('category_id', String(categoryId.value))
    if (brandId.value) formData.append('brand_id', String(brandId.value))
    formData.append('description', description.value)
    formData.append('price', price.value)
    if (salePrice.value) formData.append('sale_price', salePrice.value)
    if (stock.value !== '') formData.append('stock', stock.value)
    if (minPrice.value !== '') formData.append('min_price', minPrice.value)
    formData.append('availability', availability.value)
    formData.append('is_featured', isFeatured.value ? '1' : '0')
    formData.append('is_active', isActive.value ? '1' : '0')
    features.value.forEach((f) => formData.append('features[]', f))
    newImages.value.forEach((file) => formData.append('images[]', file))
    return formData
}

async function submit() {
    saving.value = true
    clearErrors()
    try {
        const formData = buildFormData()
        if (props.product) {
            await adminProductsService.update(props.product.id, formData)
        } else {
            await adminProductsService.create(formData)
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
    <Modal :title="product ? 'Editar producto' : 'Nuevo producto'" size="lg" @close="emit('close')">
        <form class="space-y-4" @submit.prevent="submit">
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="sm:col-span-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Nombre</label>
                    <input v-model="name" type="text" required
                        class="mt-1 w-full rounded-lg border px-3 py-2 text-sm text-gray-900 focus:outline-none dark:bg-gray-800 dark:text-gray-100"
                        :class="getError('name') ? 'border-red-400 dark:border-red-700' : 'border-gray-300 focus:border-brand-primary dark:border-gray-700'" />
                    <p v-if="getError('name')" class="mt-1 text-xs text-red-600 dark:text-red-400">{{ getError('name')
                        }}
                    </p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Código (SKU)</label>
                    <input v-model="sku" type="text" placeholder="Ej: 1233"
                        class="mt-1 w-full rounded-lg border px-3 py-2 text-sm text-gray-900 focus:outline-none dark:bg-gray-800 dark:text-gray-100"
                        :class="getError('sku') ? 'border-red-400 dark:border-red-700' : 'border-gray-300 focus:border-brand-primary dark:border-gray-700'" />
                    <p v-if="getError('sku')" class="mt-1 text-xs text-red-600 dark:text-red-400">{{ getError('sku') }}
                    </p>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Categoría</label>
                    <select v-model.number="categoryId" required
                        class="mt-1 w-full rounded-lg border px-3 py-2 text-sm text-gray-900 focus:outline-none dark:bg-gray-800 dark:text-gray-100"
                        :class="getError('category_id') ? 'border-red-400 dark:border-red-700' : 'border-gray-300 focus:border-brand-primary dark:border-gray-700'">
                        <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                    <p v-if="getError('category_id')" class="mt-1 text-xs text-red-600 dark:text-red-400">{{
                        getError('category_id') }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Marca</label>
                    <select v-model="brandId"
                        class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-primary focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                        <option :value="null">Sin marca</option>
                        <option v-for="b in brands" :key="b.id" :value="b.id">{{ b.name }}</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Descripción</label>
                <textarea v-model="description" rows="3"
                    class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-primary focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"></textarea>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Precio</label>
                    <input v-model="price" type="number" step="0.01" min="0" required
                        class="mt-1 w-full rounded-lg border px-3 py-2 text-sm text-gray-900 focus:outline-none dark:bg-gray-800 dark:text-gray-100"
                        :class="getError('price') ? 'border-red-400 dark:border-red-700' : 'border-gray-300 focus:border-brand-primary dark:border-gray-700'" />
                    <p v-if="getError('price')" class="mt-1 text-xs text-red-600 dark:text-red-400">{{ getError('price')
                    }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Precio de oferta</label>
                    <input v-model="salePrice" type="number" step="0.01" min="0"
                        class="mt-1 w-full rounded-lg border px-3 py-2 text-sm text-gray-900 focus:outline-none dark:bg-gray-800 dark:text-gray-100"
                        :class="getError('sale_price') ? 'border-red-400 dark:border-red-700' : 'border-gray-300 focus:border-brand-primary dark:border-gray-700'" />
                    <p v-if="getError('sale_price')" class="mt-1 text-xs text-red-600 dark:text-red-400">{{
                        getError('sale_price') }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Precio mínimo (interno)</label>
                    <input v-model="minPrice" type="number" step="0.01" min="0"
                        class="mt-1 w-full rounded-lg border px-3 py-2 text-sm text-gray-900 focus:outline-none dark:bg-gray-800 dark:text-gray-100"
                        :class="getError('min_price') ? 'border-red-400 dark:border-red-700' : 'border-gray-300 focus:border-brand-primary dark:border-gray-700'" />
                    <p v-if="getError('min_price')" class="mt-1 text-xs text-red-600 dark:text-red-400">{{
                        getError('min_price') }}</p>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Stock</label>
                    <input v-model="stock" type="number" step="1" min="0" placeholder="Vacío = no controlar inventario"
                        class="mt-1 w-full rounded-lg border px-3 py-2 text-sm text-gray-900 focus:outline-none dark:bg-gray-800 dark:text-gray-100"
                        :class="getError('stock') ? 'border-red-400 dark:border-red-700' : 'border-gray-300 focus:border-brand-primary dark:border-gray-700'" />
                    <p v-if="getError('stock')" class="mt-1 text-xs text-red-600 dark:text-red-400">{{ getError('stock')
                    }}</p>
                    <p v-else class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                        Si le pones un número, el producto se oculta solo del catálogo cuando llegue a 0.
                    </p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Disponibilidad</label>
                    <select v-model="availability" :disabled="stock !== ''"
                        class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-primary focus:outline-none disabled:cursor-not-allowed disabled:opacity-60 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                        <option value="in_stock">Disponible</option>
                        <option value="out_of_stock">Agotado</option>
                        <option value="on_request">Por encargo</option>
                    </select>
                    <p v-if="stock !== ''" class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                        Se calcula automáticamente a partir del stock.
                    </p>
                </div>
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Características</label>
                <div class="mt-1 flex flex-wrap gap-2">
                    <span v-for="(feature, i) in features" :key="i"
                        class="flex items-center gap-1.5 rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                        {{ feature }}
                        <button type="button" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                            @click="removeFeature(i)">
                            <XIcon class="h-3.5 w-3.5" />
                        </button>
                    </span>
                </div>
                <div class="mt-2 flex gap-2">
                    <input v-model="newFeature" type="text" placeholder="Ej: Aro 29, 21 velocidades..."
                        class="flex-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-primary focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                        @keydown.enter.prevent="addFeature" />
                    <button type="button"
                        class="rounded-lg border border-gray-300 px-3 text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                        @click="addFeature">
                        <Plus class="h-4 w-4" />
                    </button>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Imágenes</label>

                <div v-if="existingImages.length" class="mt-2 flex flex-wrap gap-2">
                    <div v-for="image in existingImages" :key="image.id"
                        class="group relative h-20 w-20 overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
                        <img :src="image.thumb" class="h-full w-full object-cover" />
                        <button type="button"
                            class="absolute inset-0 flex items-center justify-center bg-black/50 text-white opacity-0 transition group-hover:opacity-100"
                            @click="requestRemoveExistingImage(image.id)">
                            <Trash2 class="h-5 w-5" />
                        </button>
                    </div>
                </div>

                <div v-if="newImagePreviews.length" class="mt-2 flex flex-wrap gap-2">
                    <div v-for="(preview, i) in newImagePreviews" :key="preview"
                        class="group relative h-20 w-20 overflow-hidden rounded-lg border-2 border-brand-primary/40">
                        <img :src="preview" class="h-full w-full object-cover" />
                        <button type="button"
                            class="absolute inset-0 flex items-center justify-center bg-black/50 text-white opacity-0 transition group-hover:opacity-100"
                            @click="removeNewImage(i)">
                            <Trash2 class="h-5 w-5" />
                        </button>
                    </div>
                </div>

                <label for="product-images-input"
                    class="mt-2 flex cursor-pointer items-center justify-center gap-2 rounded-lg border-2 border-dashed border-gray-300 px-4 py-6 text-sm text-gray-500 transition hover:border-brand-primary hover:text-brand-primary dark:border-gray-700 dark:text-gray-400">
                    <ImagePlus class="h-5 w-5" />
                    Haz clic para subir imágenes
                </label>
                <input id="product-images-input" type="file" accept="image/*" multiple class="hidden"
                    @change="onFilesSelected" />
            </div>

            <div class="flex gap-6">
                <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                    <input v-model="isFeatured" type="checkbox"
                        class="rounded border-gray-300 text-brand-primary dark:border-gray-600 dark:bg-gray-800" />
                    Destacado
                </label>
                <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                    <input v-model="isActive" type="checkbox"
                        class="rounded border-gray-300 text-brand-primary dark:border-gray-600 dark:bg-gray-800" />
                    Activo
                </label>
            </div>

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

        <ConfirmDialog v-if="confirmDeleteImageId !== null" title="Eliminar imagen"
            message="¿Eliminar esta imagen del producto?" @confirm="confirmRemoveExistingImage"
            @cancel="confirmDeleteImageId = null" />
    </Modal>
</template>