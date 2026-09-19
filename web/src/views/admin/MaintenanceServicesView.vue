<script setup lang="ts">
import { onMounted, ref, watch } from 'vue'
import { Plus, Pencil, Trash2, Search } from '@lucide/vue'
import { adminMaintenanceServicesService } from '@/services/admin/maintenance.service'
import MaintenanceServiceFormModal from '@/components/admin/MaintenanceServiceFormModal.vue'
import ConfirmDialog from '@/components/admin/ConfirmDialog.vue'
import Pagination from '@/components/Pagination.vue'
import TableSkeletonRows from '@/components/admin/TableSkeletonRows.vue'
import { useToastStore } from '@/stores/toast'
import type { MaintenanceService } from '@/types/catalog'

const toastStore = useToastStore()

const services = ref<MaintenanceService[]>([])
const total = ref(0)
const currentPage = ref(1)
const lastPage = ref(1)
const loading = ref(true)
const showModal = ref(false)
const editingService = ref<MaintenanceService | null>(null)
const confirmDeleteService = ref<MaintenanceService | null>(null)

const search = ref('')
const status = ref<'' | 'active' | 'inactive'>('')

async function load(page = 1, opts: { silent?: boolean } = {}) {
    if (!opts.silent) loading.value = true
    const response = await adminMaintenanceServicesService.list({
        search: search.value || undefined,
        status: status.value || undefined,
        page,
    })
    services.value = response.data
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
    editingService.value = null
    showModal.value = true
}
function openEdit(service: MaintenanceService) {
    editingService.value = service
    showModal.value = true
}
async function handleSaved() {
    showModal.value = false
    toastStore.success(editingService.value ? 'Servicio actualizado.' : 'Servicio creado.')
    await load(currentPage.value, { silent: true })
}
function requestDelete(service: MaintenanceService) {
    confirmDeleteService.value = service
}
async function confirmDelete() {
    if (!confirmDeleteService.value) return
    const id = confirmDeleteService.value.id
    try {
        await adminMaintenanceServicesService.remove(id)
        const index = services.value.findIndex((s) => s.id === id)
        if (index !== -1) services.value.splice(index, 1)
        total.value = Math.max(0, total.value - 1)
        toastStore.success('Servicio eliminado.')
        if (!services.value.length && currentPage.value > 1) {
            await load(currentPage.value - 1)
        }
    } catch (err) {
        const axiosError = err as { response?: { data?: { message?: string } } }
        toastStore.error(axiosError.response?.data?.message ?? 'No se pudo eliminar el servicio.')
    } finally {
        confirmDeleteService.value = null
    }
}

function formatDuration(minutes: number) {
    if (minutes < 60) return `${minutes} min`
    const hours = Math.floor(minutes / 60)
    const rest = minutes % 60
    return rest ? `${hours}h ${rest}min` : `${hours}h`
}

onMounted(() => load())
</script>

<template>
    <div>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="font-display text-xl font-bold text-gray-900 dark:text-gray-100 sm:text-2xl">Servicios de
                    mantenimiento</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ total }} servicio{{ total === 1 ? '' : 's'
                }} en total</p>
            </div>
            <button
                class="flex items-center gap-2 rounded-full bg-brand-primary px-4 py-2 text-sm font-semibold text-white hover:brightness-110"
                @click="openCreate">
                <Plus class="h-4 w-4" />
                Nuevo servicio
            </button>
        </div>

        <div class="mt-4 flex flex-wrap gap-3">
            <div class="relative min-w-[200px] flex-1">
                <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                <input v-model="search" type="search" placeholder="Buscar por nombre..."
                    class="w-full rounded-full border border-gray-200 bg-white py-2 pl-9 pr-4 text-sm text-gray-900 focus:border-brand-primary focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" />
            </div>
            <select v-model="status"
                class="rounded-full border border-gray-200 bg-white px-4 py-2 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                <option value="">Todos los estados</option>
                <option value="active">Activos</option>
                <option value="inactive">Inactivos</option>
            </select>
        </div>

        <div class="mt-6 rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[560px] text-left text-sm">
                    <thead
                        class="border-b border-gray-200 bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:border-gray-800 dark:bg-gray-800/60 dark:text-gray-400">
                        <tr>
                            <th class="px-4 py-3">Servicio</th>
                            <th class="px-4 py-3">Duración</th>
                            <th class="px-4 py-3">Precio</th>
                            <th class="px-4 py-3">Estado</th>
                            <th class="px-4 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <TableSkeletonRows v-if="loading" :columns="5" />
                        <tr v-else-if="!services.length">
                            <td colspan="5" class="px-4 py-8 text-center text-gray-400 dark:text-gray-500">No se
                                encontraron servicios.</td>
                        </tr>
                        <template v-else>
                            <tr v-for="service in services" :key="service.id">
                                <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">{{ service.name }}
                                </td>
                                <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{
                                    formatDuration(service.duration_minutes) }}</td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ service.price ? `S/
                                    ${service.price}` : '—' }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-medium"
                                        :class="service.is_active ? 'bg-green-50 text-green-700 dark:bg-green-950/40 dark:text-green-400' : 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400'">
                                        {{ service.is_active ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        <button
                                            class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800"
                                            @click="openEdit(service)">
                                            <Pencil class="h-4 w-4" />
                                        </button>
                                        <button
                                            class="rounded-lg p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-950/40"
                                            @click="requestDelete(service)">
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

        <MaintenanceServiceFormModal v-if="showModal" :service="editingService" @close="showModal = false"
            @saved="handleSaved" />
        <ConfirmDialog v-if="confirmDeleteService" title="Eliminar servicio"
            :message="`¿Eliminar el servicio &quot;${confirmDeleteService.name}&quot;? Esta acción no se puede deshacer.`"
            @confirm="confirmDelete" @cancel="confirmDeleteService = null" />
    </div>
</template>