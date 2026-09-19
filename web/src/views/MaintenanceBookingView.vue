<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { useHead } from '@unhead/vue'
import { Check, Clock, Wrench } from '@lucide/vue'
import { catalogService } from '@/services/catalog.service'
import { useSettingsStore } from '@/stores/settings'
import { useToastStore } from '@/stores/toast'
import { useFormErrors } from '@/composables/useFormErrors'
import { buildWhatsAppUrl } from '@/utils/whatsapp'
import { formatCurrency } from '@/utils/currency'
import Breadcrumbs from '@/components/Breadcrumbs.vue'
import type { MaintenanceService, MaintenanceAppointment } from '@/types/catalog'
import { trackEvent } from '@/utils/analytics'

useHead(() => ({ title: 'Reservar mantenimiento' }))

const settingsStore = useSettingsStore()
const toastStore = useToastStore()
const { getError, parseErrors, clearErrors } = useFormErrors()

const services = ref<MaintenanceService[]>([])
const loadingServices = ref(true)

const selectedServiceId = ref<number | null>(null)
const selectedDate = ref('')
const selectedTime = ref<string | null>(null)
const availableSlots = ref<string[]>([])
const loadingSlots = ref(false)

const customerName = ref('')
const customerPhone = ref('')
const bikeInfo = ref('')

const submitting = ref(false)
const confirmedAppointment = ref<MaintenanceAppointment | null>(null)

const minDate = computed(() => new Date().toISOString().slice(0, 10))
const maxDate = computed(() => {
    const d = new Date()
    d.setDate(d.getDate() + 60)
    return d.toISOString().slice(0, 10)
})

function formatDuration(minutes: number) {
    if (minutes < 60) return `${minutes} min`
    const hours = Math.floor(minutes / 60)
    const rest = minutes % 60
    return rest ? `${hours}h ${rest}min` : `${hours}h`
}

async function loadSlots() {
    if (!selectedServiceId.value || !selectedDate.value) {
        availableSlots.value = []
        return
    }
    loadingSlots.value = true
    selectedTime.value = null
    try {
        availableSlots.value = await catalogService.getMaintenanceAvailability(selectedServiceId.value, selectedDate.value)
    } finally {
        loadingSlots.value = false
    }
}

watch([selectedServiceId, selectedDate], loadSlots)

async function submit() {
    if (!selectedServiceId.value || !selectedDate.value || !selectedTime.value) return
    submitting.value = true
    clearErrors()
    try {
        confirmedAppointment.value = await catalogService.createAppointment({
            maintenance_service_id: selectedServiceId.value,
            date: selectedDate.value,
            time: selectedTime.value,
            customer_name: customerName.value,
            customer_phone: customerPhone.value,
            bike_info: bikeInfo.value || undefined,
        })
        toastStore.success('¡Cita reservada con éxito!')

        const service = services.value.find((s) => s.id === selectedServiceId.value)
        trackEvent('book_maintenance', {
            currency: 'PEN',
            value: service?.price ? Number(service.price) : undefined,
            item_name: service?.name,
        })
    } catch (err) {
        const message = parseErrors(err)
        toastStore.error(message)
        if ((err as { response?: { status?: number } }).response?.status === 422) {
            await loadSlots()
        }
    } finally {
        submitting.value = false
    }
}

const confirmationWhatsappUrl = computed(() => {
    const phone = settingsStore.settings?.whatsapp_number
    if (!phone || !confirmedAppointment.value) return null
    const a = confirmedAppointment.value
    const date = new Date(a.starts_at).toLocaleDateString('es-PE', { dateStyle: 'long' })
    const time = new Date(a.starts_at).toLocaleTimeString('es-PE', { timeStyle: 'short' })
    const lines = [
        'Hola, acabo de reservar una cita de mantenimiento:',
        `Servicio: ${a.service.name}`,
        `Fecha: ${date} a las ${time}`,
        `Nombre: ${a.customer_name}`,
        `Teléfono: ${a.customer_phone}`,
    ]
    if (a.bike_info) lines.push(`Bicicleta: ${a.bike_info}`)
    return buildWhatsAppUrl(phone, lines.join('\n'))
})

onMounted(async () => {
    services.value = await catalogService.getMaintenanceServices()
    loadingServices.value = false
})
</script>

<template>
    <div class="mx-auto max-w-[1400px] px-4 py-6 sm:py-8">
        <Breadcrumbs :items="[{ label: 'Inicio', to: '/' }, { label: 'Reservar mantenimiento' }]" />

        <h1 class="font-display text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">Reserva tu
            mantenimiento</h1>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Elige un servicio, la fecha y el horario que más te
            convenga.</p>

        <div v-if="confirmedAppointment"
            class="mx-auto mt-8 max-w-md rounded-2xl border border-green-200 bg-green-50 p-6 text-center dark:border-green-900 dark:bg-green-950/30">
            <div
                class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-green-100 text-green-600 dark:bg-green-900/50 dark:text-green-400">
                <Check class="h-6 w-6" />
            </div>
            <h2 class="mt-3 font-display text-lg font-semibold text-gray-900 dark:text-gray-100">¡Cita reservada!</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Guardamos tu horario. Confírmalo por WhatsApp para que quede coordinado con nosotros.
            </p>

            <a v-if="confirmationWhatsappUrl" :href="confirmationWhatsappUrl" target="_blank" rel="noopener" class="mt-4 inline-flex items-center gap-2 rounded-full bg-[#25D366] px-6 py-3 font-display font-semibold
            text-white transition hover:brightness-95">
                <svg viewBox="0 0 24 24" class="h-5 w-5" fill="currentColor">
                    <path
                        d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.79.47 3.43 1.29 4.9L2 22l5.29-1.39c1.4.76 3 1.2 4.7 1.2h.01c5.46 0 9.91-4.45 9.91-9.91S17.5 2 12.04 2zm0 18.05h-.01c-1.6 0-3.15-.43-4.5-1.24l-.32-.19-3.13.82.84-3.05-.21-.32a8.02 8.02 0 0 1-1.24-4.26c0-4.46 3.63-8.09 8.09-8.09 4.46 0 8.09 3.63 8.09 8.09 0 4.46-3.63 8.05-8.11 8.05zm4.44-6.02c-.24-.12-1.44-.71-1.66-.79-.22-.08-.38-.12-.55.12-.16.24-.62.79-.76.95-.14.16-.28.18-.52.06-.24-.12-1.01-.37-1.92-1.18-.71-.63-1.19-1.41-1.33-1.65-.14-.24-.02-.37.1-.49.11-.11.24-.28.36-.42.12-.14.16-.24.24-.4.08-.16.04-.3-.02-.42-.06-.12-.55-1.33-.76-1.82-.2-.48-.4-.42-.55-.42-.14 0-.3-.02-.46-.02s-.42.06-.64.3c-.22.24-.85.83-.85 2.02 0 1.19.87 2.34 1 2.5.12.16 1.71 2.61 4.15 3.66.58.25 1.03.4 1.38.51.58.18 1.11.16 1.53.1.47-.07 1.44-.59 1.64-1.16.2-.57.2-1.06.14-1.16-.06-.1-.22-.16-.46-.28z" />
                </svg>
                Confirmar por WhatsApp
            </a>
        </div>

        <form v-else class="mt-8 grid gap-8 lg:grid-cols-[1.3fr_1fr] lg:items-start" @submit.prevent="submit">
            <div class="space-y-8">
                <div>
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">1. Elige un servicio</h2>
                    <div v-if="loadingServices" class="mt-3 text-sm text-gray-400 dark:text-gray-500">Cargando
                        servicios...
                    </div>
                    <div v-else-if="!services.length" class="mt-3 text-sm text-gray-400 dark:text-gray-500">Aún no hay
                        servicios disponibles.</div>
                    <div v-else class="mt-3 grid gap-3 sm:grid-cols-2">
                        <button v-for="service in services" :key="service.id" type="button"
                            class="rounded-xl border p-4 text-left transition" :class="selectedServiceId === service.id
                                ? 'border-brand-primary bg-brand-primary/5'
                                : 'border-gray-200 hover:border-gray-300 dark:border-gray-700 dark:hover:border-gray-600'
                                " @click="selectedServiceId = service.id">
                            <div class="flex items-center gap-2 text-gray-900 dark:text-gray-100">
                                <Wrench class="h-4 w-4 text-brand-primary" />
                                <span class="font-medium">{{ service.name }}</span>
                            </div>
                            <p v-if="service.description" class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{
                                service.description }}</p>
                            <div class="mt-2 flex items-center gap-3 text-xs text-gray-400 dark:text-gray-500">
                                <span class="flex items-center gap-1">
                                    <Clock class="h-3.5 w-3.5" /> {{ formatDuration(service.duration_minutes) }}
                                </span>
                                <span v-if="service.price">{{ formatCurrency(service.price) }}</span>
                            </div>
                        </button>
                    </div>
                </div>

                <div v-if="selectedServiceId">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">2. Elige una fecha</h2>
                    <input v-model="selectedDate" type="date" :min="minDate" :max="maxDate" required
                        class="mt-3 w-full max-w-xs rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-primary focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100" />
                </div>

                <div v-if="selectedServiceId && selectedDate">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">3. Elige un horario</h2>
                    <div v-if="loadingSlots" class="mt-3 text-sm text-gray-400 dark:text-gray-500">Buscando horarios
                        disponibles...</div>
                    <div v-else-if="!availableSlots.length" class="mt-3 text-sm text-gray-400 dark:text-gray-500">
                        No hay horarios disponibles ese día. Prueba con otra fecha.
                    </div>
                    <div v-else class="mt-3 flex flex-wrap gap-2">
                        <button v-for="slot in availableSlots" :key="slot" type="button"
                            class="rounded-full border px-4 py-2 text-sm transition" :class="selectedTime === slot
                                ? 'border-brand-primary bg-brand-primary text-white'
                                : 'border-gray-200 text-gray-700 hover:border-brand-primary hover:text-brand-primary dark:border-gray-700 dark:text-gray-300'
                                " @click="selectedTime = slot">
                            {{ slot }}
                        </button>
                    </div>
                </div>
            </div>

            <div
                class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 lg:sticky lg:top-24">
                <template v-if="selectedTime">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">4. Tus datos</h2>
                    <div class="mt-4 space-y-4">
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Nombre completo</label>
                            <input v-model="customerName" type="text" required
                                class="mt-1 w-full rounded-lg border px-3 py-2 text-sm text-gray-900 focus:outline-none dark:bg-gray-800 dark:text-gray-100"
                                :class="getError('customer_name') ? 'border-red-400 dark:border-red-700' : 'border-gray-300 focus:border-brand-primary dark:border-gray-700'" />
                            <p v-if="getError('customer_name')" class="mt-1 text-xs text-red-600 dark:text-red-400">{{
                                getError('customer_name') }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">WhatsApp /
                                Teléfono</label>
                            <input v-model="customerPhone" type="text" required placeholder="987654321"
                                class="mt-1 w-full rounded-lg border px-3 py-2 text-sm text-gray-900 focus:outline-none dark:bg-gray-800 dark:text-gray-100"
                                :class="getError('customer_phone') ? 'border-red-400 dark:border-red-700' : 'border-gray-300 focus:border-brand-primary dark:border-gray-700'" />
                            <p v-if="getError('customer_phone')" class="mt-1 text-xs text-red-600 dark:text-red-400">{{
                                getError('customer_phone') }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Modelo de tu bicicleta
                                (opcional)</label>
                            <input v-model="bikeInfo" type="text" placeholder="Ej: MTB aro 29, marca Trek"
                                class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-primary focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100" />
                        </div>

                        <button type="submit" :disabled="submitting"
                            class="w-full rounded-full bg-brand-primary py-3 font-display font-semibold text-white transition hover:brightness-110 disabled:opacity-60">
                            {{ submitting ? 'Reservando...' : 'Reservar cita' }}
                        </button>
                    </div>
                </template>

                <div v-else class="flex flex-col items-center py-6 text-center text-gray-400 dark:text-gray-500">
                    <Wrench class="h-8 w-8" />
                    <p class="mt-3 text-sm">Completa los pasos anteriores para dejar tus datos y confirmar la cita.</p>
                </div>
            </div>
        </form>
    </div>
</template>