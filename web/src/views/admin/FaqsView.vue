<script setup lang="ts">
import { onMounted, ref, watch } from 'vue'
import { Plus, Pencil, Trash2, Search } from '@lucide/vue'
import { adminFaqsService } from '@/services/admin/faqs.service'
import FaqFormModal from '@/components/admin/FaqFormModal.vue'
import ConfirmDialog from '@/components/admin/ConfirmDialog.vue'
import Pagination from '@/components/Pagination.vue'
import TableSkeletonRows from '@/components/admin/TableSkeletonRows.vue'
import { useToastStore } from '@/stores/toast'
import type { Faq } from '@/types/catalog'

const toastStore = useToastStore()

const faqs = ref<Faq[]>([])
const total = ref(0)
const currentPage = ref(1)
const lastPage = ref(1)
const loading = ref(true)
const showModal = ref(false)
const editingFaq = ref<Faq | null>(null)
const confirmDeleteFaq = ref<Faq | null>(null)

const search = ref('')
const status = ref<'' | 'active' | 'inactive'>('')

async function load(page = 1, opts: { silent?: boolean } = {}) {
    if (!opts.silent) loading.value = true
    const response = await adminFaqsService.list({
        search: search.value || undefined,
        status: status.value || undefined,
        page,
    })
    faqs.value = response.data
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
watch(status, () => load(1))

function openCreate() {
    editingFaq.value = null
    showModal.value = true
}
function openEdit(faq: Faq) {
    editingFaq.value = faq
    showModal.value = true
}
async function handleSaved() {
    showModal.value = false
    toastStore.success(editingFaq.value ? 'Pregunta actualizada.' : 'Pregunta creada.')
    await load(currentPage.value, { silent: true })
}
function requestDelete(faq: Faq) {
    confirmDeleteFaq.value = faq
}
async function confirmDelete() {
    if (!confirmDeleteFaq.value) return
    const id = confirmDeleteFaq.value.id
    await adminFaqsService.remove(id)
    confirmDeleteFaq.value = null
    const index = faqs.value.findIndex((f) => f.id === id)
    if (index !== -1) faqs.value.splice(index, 1)
    total.value = Math.max(0, total.value - 1)
    toastStore.success('Pregunta eliminada.')
    if (!faqs.value.length && currentPage.value > 1) {
        await load(currentPage.value - 1)
    }
}

onMounted(() => load())
</script>

<template>
    <div>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="font-display text-xl font-bold text-gray-900 dark:text-gray-100 sm:text-2xl">Preguntas
                    frecuentes</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ total }} pregunta{{ total === 1 ? '' : 's'
                }} en total</p>
            </div>
            <button
                class="flex items-center gap-2 rounded-full bg-brand-primary px-4 py-2 text-sm font-semibold text-white hover:brightness-110"
                @click="openCreate">
                <Plus class="h-4 w-4" />
                Nueva pregunta
            </button>
        </div>

        <div class="mt-4 flex flex-wrap gap-3">
            <div class="relative min-w-[200px] flex-1">
                <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                <input v-model="search" type="search" placeholder="Buscar por pregunta..."
                    class="w-full rounded-full border border-gray-200 bg-white py-2 pl-9 pr-4 text-sm text-gray-900 focus:border-brand-primary focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" />
            </div>
            <select v-model="status"
                class="rounded-full border border-gray-200 bg-white px-4 py-2 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                <option value="">Todos los estados</option>
                <option value="active">Activas</option>
                <option value="inactive">Inactivas</option>
            </select>
        </div>

        <div class="mt-6 rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[560px] text-left text-sm">
                    <thead
                        class="border-b border-gray-200 bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:border-gray-800 dark:bg-gray-800/60 dark:text-gray-400">
                        <tr>
                            <th class="px-4 py-3">Pregunta</th>
                            <th class="px-4 py-3">Orden</th>
                            <th class="px-4 py-3">Estado</th>
                            <th class="px-4 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <TableSkeletonRows v-if="loading" :columns="4" />
                        <tr v-else-if="!faqs.length">
                            <td colspan="4" class="px-4 py-8 text-center text-gray-400 dark:text-gray-500">No se
                                encontraron preguntas.</td>
                        </tr>
                        <template v-else>
                            <tr v-for="faq in faqs" :key="faq.id">
                                <td class="max-w-md truncate px-4 py-3 font-medium text-gray-900 dark:text-gray-100">{{
                                    faq.question }}</td>
                                <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ faq.order }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-medium"
                                        :class="faq.is_active ? 'bg-green-50 text-green-700 dark:bg-green-950/40 dark:text-green-400' : 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400'">
                                        {{ faq.is_active ? 'Activa' : 'Inactiva' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        <button
                                            class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800"
                                            @click="openEdit(faq)">
                                            <Pencil class="h-4 w-4" />
                                        </button>
                                        <button
                                            class="rounded-lg p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-950/40"
                                            @click="requestDelete(faq)">
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

        <FaqFormModal v-if="showModal" :faq="editingFaq" @close="showModal = false" @saved="handleSaved" />
        <ConfirmDialog v-if="confirmDeleteFaq" title="Eliminar pregunta"
            message="¿Eliminar esta pregunta frecuente? Esta acción no se puede deshacer." @confirm="confirmDelete"
            @cancel="confirmDeleteFaq = null" />
    </div>
</template>