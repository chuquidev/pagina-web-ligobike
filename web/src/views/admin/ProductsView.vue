<script setup lang="ts">
import { onMounted, ref, watch } from 'vue'
import { Plus, Pencil, Trash2, Eye, Package, Search, Upload } from '@lucide/vue'
import { adminProductsService } from '@/services/admin/products.service'
import { useCatalogStore } from '@/stores/catalog'
import ProductFormModal from '@/components/admin/ProductFormModal.vue'
import ProductImportModal from '@/components/admin/ProductImportModal.vue'
import ConfirmDialog from '@/components/admin/ConfirmDialog.vue'
import Pagination from '@/components/Pagination.vue'
import TableSkeletonRows from '@/components/admin/TableSkeletonRows.vue'
import type { Product } from '@/types/catalog'
import AvailabilityBadge from '@/components/AvailabilityBadge.vue'
import { useToastStore } from '@/stores/toast'


const catalogStore = useCatalogStore()
const products = ref<Product[]>([])
const total = ref(0)
const currentPage = ref(1)
const lastPage = ref(1)
const loading = ref(true)
const showModal = ref(false)
const showImportModal = ref(false)
const editingProduct = ref<Product | null>(null)
const confirmDeleteProduct = ref<Product | null>(null)
const toastStore = useToastStore()
const search = ref('')
const categoryId = ref<number | ''>('')
const brandId = ref<number | ''>('')
const status = ref<'' | 'active' | 'inactive'>('')

async function load(page = 1, opts: { silent?: boolean } = {}) {
    if (!opts.silent) loading.value = true
    const response = await adminProductsService.list({
        search: search.value || undefined,
        category: categoryId.value || undefined,
        brand: brandId.value || undefined,
        status: status.value || undefined,
        page,
    })
    products.value = response.data
    total.value = response.meta.total
    currentPage.value = response.meta.current_page
    lastPage.value = response.meta.last_page
    if (!opts.silent) loading.value = false
}

let searchTimeout: ReturnType<typeof setTimeout>

watch(search, () => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => load(1), 350)
})

watch([categoryId, brandId, status], () => load(1))

function openCreate() {
    editingProduct.value = null
    showModal.value = true
}

function openEdit(product: Product) {
    editingProduct.value = product
    showModal.value = true
}

async function handleSaved() {
    showModal.value = false
    toastStore.success(editingProduct.value ? 'Producto actualizado.' : 'Producto creado.')
    await load(currentPage.value, { silent: true })
}

function requestDelete(product: Product) {
    confirmDeleteProduct.value = product
}

async function confirmDelete() {
    if (!confirmDeleteProduct.value) return
    const id = confirmDeleteProduct.value.id
    await adminProductsService.remove(id)
    confirmDeleteProduct.value = null

    const index = products.value.findIndex((p) => p.id === id)
    if (index !== -1) products.value.splice(index, 1)
    total.value = Math.max(0, total.value - 1)
    toastStore.success('Producto eliminado.')

    if (!products.value.length && currentPage.value > 1) {
        await load(currentPage.value - 1)
    }
}

async function toggleActive(product: Product) {
    const updated = await adminProductsService.toggleActive(product.id)
    const index = products.value.findIndex((p) => p.id === product.id)
    if (index !== -1) products.value[index] = updated
    toastStore.info(updated.is_active ? 'Producto activado.' : 'Producto desactivado.')
}

async function handleImported() {
    showImportModal.value = false
    await load(1)
}

onMounted(async () => {
    await catalogStore.fetch()
    await load()
})
</script>

<template>
    <div>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="font-display text-xl font-bold text-gray-900 dark:text-gray-100 sm:text-2xl">Productos</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ total }} producto{{ total === 1 ? '' : 's'
                }} en total</p>
            </div>
            <div class="flex items-center gap-2">
                <button
                    class="flex items-center gap-2 rounded-full border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                    @click="showImportModal = true">
                    <Upload class="h-4 w-4" />
                    Importar Excel
                </button>
                <button
                    class="flex items-center gap-2 rounded-full bg-brand-primary px-4 py-2 text-sm font-semibold text-white hover:brightness-110"
                    @click="openCreate">
                    <Plus class="h-4 w-4" />
                    Nuevo producto
                </button>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap gap-3">
            <div class="relative min-w-[200px] flex-1">
                <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                <input v-model="search" type="search" placeholder="Buscar por nombre..."
                    class="w-full rounded-full border border-gray-200 bg-white py-2 pl-9 pr-4 text-sm text-gray-900 focus:border-brand-primary focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" />
            </div>
            <select v-model="categoryId"
                class="rounded-full border border-gray-200 bg-white px-4 py-2 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                <option value="">Todas las categorías</option>
                <option v-for="c in catalogStore.categories" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
            <select v-model="brandId"
                class="rounded-full border border-gray-200 bg-white px-4 py-2 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                <option value="">Todas las marcas</option>
                <option v-for="b in catalogStore.brands" :key="b.id" :value="b.id">{{ b.name }}</option>
            </select>
            <select v-model="status"
                class="rounded-full border border-gray-200 bg-white px-4 py-2 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                <option value="">Todos los estados</option>
                <option value="active">Activos</option>
                <option value="inactive">Inactivos</option>
            </select>
        </div>

        <div class="mt-6 rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[720px] text-left text-sm">
                    <thead
                        class="border-b border-gray-200 bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:border-gray-800 dark:bg-gray-800/60 dark:text-gray-400">
                        <tr>
                            <th class="px-4 py-3">Producto</th>
                            <th class="px-4 py-3">Código</th>
                            <th class="px-4 py-3">Categoría</th>
                            <th class="px-4 py-3">Marca</th>
                            <th class="px-4 py-3">Precio</th>
                            <th class="px-4 py-3">Precio de oferta</th>
                            <th class="px-4 py-3">Stock</th>
                            <th class="px-4 py-3">Disponibilidad</th>
                            <th class="px-4 py-3">Estado</th>
                            <th class="px-4 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <TableSkeletonRows v-if="loading" :columns="10" />
                        <tr v-else-if="!products.length">
                            <td colspan="10" class="px-4 py-8 text-center text-gray-400 dark:text-gray-500">No se
                                encontraron productos.</td>
                        </tr>
                        <template v-else>
                            <tr v-for="product in products" :key="product.id">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="h-10 w-10 shrink-0 overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-800">
                                            <img v-if="product.images[0]" :src="product.images[0].thumb"
                                                class="h-full w-full object-cover" />
                                            <div v-else
                                                class="flex h-full w-full items-center justify-center text-gray-300 dark:text-gray-600">
                                                <Package class="h-5 w-5" />
                                            </div>
                                        </div>
                                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ product.name
                                        }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ product.sku ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ product.category.name }}</td>
                                <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ product.brand?.name ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">S/ {{ product.price }}</td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ product.sale_price ? `S/
                                    ${product.sale_price}` : '—' }}</td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                                    <span v-if="product.stock !== null"
                                        :class="product.stock === 0 ? 'text-red-500' : ''">{{ product.stock }}</span>
                                    <span v-else class="text-gray-400 dark:text-gray-600">—</span>
                                </td>
                                <td class="px-4 py-3">
                                    <AvailabilityBadge :availability="product.availability" />
                                </td>
                                <td class="px-4 py-3">
                                    <button class="rounded-full px-2.5 py-1 text-xs font-medium transition"
                                        :class="product.is_active ? 'bg-green-50 text-green-700 dark:bg-green-950/40 dark:text-green-400' : 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400'"
                                        @click="toggleActive(product)">
                                        {{ product.is_active ? 'Activo' : 'Inactivo' }}
                                    </button>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        <a :href="`/producto/${product.slug}`" target="_blank" rel="noopener" class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 dark:text-gray-400
                                        dark:hover:bg-gray-800" title="Ver como cliente">
                                            <Eye class="h-4 w-4" />
                                        </a>
                                        <button
                                            class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800"
                                            @click="openEdit(product)">
                                            <Pencil class="h-4 w-4" />
                                        </button>
                                        <button
                                            class="rounded-lg p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-950/40"
                                            @click="requestDelete(product)">
                                            <Trash2 class="h-4 w-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <Pagination :current-page="currentPage" :last-page="lastPage" :total="total" @change="load" />

        <ProductFormModal v-if="showModal" :product="editingProduct" :categories="catalogStore.categories"
            :brands="catalogStore.brands" @close="showModal = false" @saved="handleSaved" />
        <ProductImportModal v-if="showImportModal" @close="showImportModal = false" @imported="handleImported" />
        <ConfirmDialog v-if="confirmDeleteProduct" title="Eliminar producto"
            :message="`¿Eliminar &quot;${confirmDeleteProduct.name}&quot;? Esta acción no se puede deshacer.`"
            @confirm="confirmDelete" @cancel="confirmDeleteProduct = null" />
    </div>
</template>