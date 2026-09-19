<script setup lang="ts">
import { onMounted, ref, watch } from 'vue'
import { Search, Trash2, MessageCircle } from '@lucide/vue'
import { adminMaintenanceAppointmentsService } from '@/services/admin/maintenance.service'
import ConfirmDialog from '@/components/admin/ConfirmDialog.vue'
import Pagination from '@/components/Pagination.vue'
import TableSkeletonRows from '@/components/admin/TableSkeletonRows.vue'
import { useToastStore } from '@/stores/toast'
import { buildWhatsAppUrl } from '@/utils/whatsapp'
import type { MaintenanceAppointment, AppointmentStatus } from '@/types/catalog'

const toastStore = useToastStore()

const appointments = ref<MaintenanceAppointment[]>([])
const total = ref(0)
const currentPage = ref(1)
const lastPage = ref(1)
const loading = ref(true)
const confirmDeleteAppointment = ref<MaintenanceAppointment | null>(null)

const search = ref('')
const status = ref<'' | AppointmentStatus>('')
const date = ref('')

const statusStyles: Record<AppointmentStatus, string> = {
    pending: 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400',
    confirmed: 'bg-green-50 text-green-700 dark:bg-green-950/40 dark:text-green-400',
    cancelled: 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400',
    completed: 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400',
}

const statusLabels: Record<AppointmentStatus, string> = {
    pending: 'Pendiente',
    confirmed: 'Confirmada',
    cancelled: 'Cancelada',
    completed: 'Completada',
}

async function load(page = 1, opts: { silent?: boolean } = {}) {
    if (!opts.silent) loading.value = true
    const response = await adminMaintenanceAppointmentsService.list({
        search: search.value || undefined,
        status: status.value || undefined,
        date: date.value || undefined,
        page,
    })
    appointments.value = response.data
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
watch([status, date], () => load(1))

async function changeStatus(appointment: MaintenanceAppointment, newStatus: string) {
    const updated = await adminMaintenanceAppointmentsService.updateStatus(appointment.id, newStatus as AppointmentStatus)
    const index = appointments.value.findIndex((a) => a.id === appointment.id)
    if (index !== -1) appointments.value[index] = updated
    toastStore.info(`Cita marcada como "${statusLabels[updated.status]}".`)
}

function requestDelete(appointment: MaintenanceAppointment) {
    confirmDeleteAppointment.value = appointment
}
async function confirmDelete() {
    if (!confirmDeleteAppointment.value) return
    const id = confirmDeleteAppointment.value.id
    await adminMaintenanceAppointmentsService.remove(id)
    confirmDeleteAppointment.value = null
    const index = appointments.value.findIndex((a) => a.id === id)
    if (index !== -1) appointments.value.splice(index, 1)
    total.value = Math.max(0, total.value - 1)
    toastStore.success('Cita eliminada.')
}

function whatsappLink(appointment: MaintenanceAppointment) {
    return buildWhatsAppUrl(appointment.customer_phone, `Hola ${appointment.customer_name}, te escribimos por tu cita de mantenimiento.`)
}

function formatDateTime(iso: string) {
    return new Date(iso).toLocaleString('es-PE', { dateStyle: 'medium', timeStyle: 'short' })
}

onMounted(() => load())
</script>

<template>
    <div>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="font-display text-xl font-bold text-gray-900 dark:text-gray-100 sm:text-2xl">Citas de
                    mantenimiento</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ total }} cita{{ total === 1 ? '' : 's' }} en
                    total</p>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap gap-3">
            <div class="relative min-w-[200px] flex-1">
                <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                <input v-model="search" type="search" placeholder="Buscar por cliente..."
                    class="w-full rounded-full border border-gray-200 bg-white py-2 pl-9 pr-4 text-sm text-gray-900 focus:border-brand-primary focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" />
            </div>
            <input v-model="date" type="date"
                class="rounded-full border border-gray-200 bg-white px-4 py-2 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300" />
            <select v-model="status"
                class="rounded-full border border-gray-200 bg-white px-4 py-2 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                <option value="">Todos los estados</option>
                <option value="pending">Pendientes</option>
                <option value="confirmed">Confirmadas</option>
                <option value="completed">Completadas</option>
                <option value="cancelled">Canceladas</option>
            </select>
        </div>

        <div class="mt-6 rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-left text-sm">
                    <thead
                        class="border-b border-gray-200 bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:border-gray-800 dark:bg-gray-800/60 dark:text-gray-400">
                        <tr>
                            <th class="px-4 py-3">Cliente</th>
                            <th class="px-4 py-3">Servicio</th>
                            <th class="px-4 py-3">Fecha y hora</th>
                            <th class="px-4 py-3">Estado</th>
                            <th class="px-4 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <TableSkeletonRows v-if="loading" :columns="5" />
                        <tr v-else-if="!appointments.length">
                            <td colspan="5" class="px-4 py-8 text-center text-gray-400 dark:text-gray-500">No se
                                encontraron citas.</td>
                        </tr>
                        <template v-else>
                            <tr v-for="appointment in appointments" :key="appointment.id">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ appointment.customer_name
                                    }}</p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500">{{ appointment.customer_phone }}
                                    </p>
                                    <p v-if="appointment.bike_info" class="text-xs text-gray-400 dark:text-gray-500">{{
                                        appointment.bike_info }}</p>
                                </td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ appointment.service.name }}
                                </td>
                                <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{
                                    formatDateTime(appointment.starts_at) }}</td>
                                <td class="px-4 py-3">
                                    <select :value="appointment.status"
                                        class="rounded-full border-0 px-2.5 py-1 text-xs font-medium"
                                        :class="statusStyles[appointment.status]"
                                        @change="changeStatus(appointment, ($event.target as HTMLSelectElement).value)">
                                        <option value="pending">Pendiente</option>
                                        <option value="confirmed">Confirmada</option>
                                        <option value="completed">Completada</option>
                                        <option value="cancelled">Cancelada</option>
                                    </select>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        <a :href="whatsappLink(appointment)" target="_blank" rel="noopener" class="rounded-lg p-2 text-green-600 hover:bg-green-50
                                        dark:hover:bg-green-950/40" title="Escribir por WhatsApp">
                                            <MessageCircle class="h-4 w-4" />
                                        </a>
                                        <button
                                            class="rounded-lg p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-950/40"
                                            @click="requestDelete(appointment)">
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

        <ConfirmDialog v-if="confirmDeleteAppointment" title="Eliminar cita"
            message="¿Eliminar esta cita? Esta acción no se puede deshacer." @confirm="confirmDelete"
            @cancel="confirmDeleteAppointment = null" />
    </div>
</template>