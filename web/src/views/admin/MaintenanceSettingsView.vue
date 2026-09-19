<script setup lang="ts">
import { onMounted, ref } from 'vue'

import { adminMaintenanceSettingsService } from '@/services/admin/maintenance.service'
import { useToastStore } from '@/stores/toast'
import { useFormErrors } from '@/composables/useFormErrors'
import type { MaintenanceSettings, DayKey } from '@/types/catalog'

const toastStore = useToastStore()
const { getError, parseErrors, clearErrors } = useFormErrors()

const days: { key: DayKey; label: string }[] = [
    { key: 'monday', label: 'Lunes' },
    { key: 'tuesday', label: 'Martes' },
    { key: 'wednesday', label: 'Miércoles' },
    { key: 'thursday', label: 'Jueves' },
    { key: 'friday', label: 'Viernes' },
    { key: 'saturday', label: 'Sábado' },
    { key: 'sunday', label: 'Domingo' },
]

const hours = ref<Record<DayKey, { open: string; close: string } | null>>({
    monday: null,
    tuesday: null,
    wednesday: null,
    thursday: null,
    friday: null,
    saturday: null,
    sunday: null,
})

const capacity = ref(1)
const slotIntervalMinutes = ref(30)
const advanceBookingDays = ref(14)
const minNoticeHours = ref(2)

const loading = ref(true)
const saving = ref(false)

function toggleDay(day: DayKey, open: boolean) {
    hours.value[day] = open
        ? { open: '09:00', close: '19:00' }
        : null
}

async function load() {
    loading.value = true

    try {
        const settings = await adminMaintenanceSettingsService.get()

        hours.value = settings.business_hours
        capacity.value = settings.capacity
        slotIntervalMinutes.value = settings.slot_interval_minutes
        advanceBookingDays.value = settings.advance_booking_days
        minNoticeHours.value = settings.min_notice_hours
    } catch (err) {
        toastStore.error(parseErrors(err))
    } finally {
        loading.value = false
    }
}

async function submit() {
    saving.value = true
    clearErrors()

    try {
        const payload: MaintenanceSettings = {
            business_hours: hours.value,
            capacity: capacity.value,
            slot_interval_minutes: slotIntervalMinutes.value,
            advance_booking_days: advanceBookingDays.value,
            min_notice_hours: minNoticeHours.value,
        }

        await adminMaintenanceSettingsService.update(payload)

        toastStore.success('Horarios guardados.')
    } catch (err) {
        toastStore.error(parseErrors(err))
    } finally {
        saving.value = false
    }
}

onMounted(load)
</script>

<template>
    <div class="w-full min-w-0">

        <!-- TÍTULO -->
        <div class="mb-6">
            <h1 class="font-display text-xl font-bold text-gray-900 dark:text-gray-100 sm:text-2xl">
                Horarios de atención
            </h1>

            <p class="mt-1 max-w-3xl text-sm leading-6 text-gray-500 dark:text-gray-400">
                Define cuándo se pueden reservar citas de mantenimiento y cuántas
                al mismo tiempo.
            </p>
        </div>

        <!-- LOADING -->
        <div v-if="loading"
            class="w-full max-w-7xl animate-pulse space-y-6 rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900 sm:p-6 lg:p-7">
            <div class="h-4 w-40 rounded bg-gray-100 dark:bg-gray-800"></div>
            <div class="space-y-3">
                <div v-for="i in 7" :key="i" class="h-10 rounded-lg bg-gray-100 dark:bg-gray-800"></div>
            </div>
            <div class="h-4 w-32 rounded bg-gray-100 dark:bg-gray-800"></div>
            <div class="grid gap-4 sm:grid-cols-3">
                <div v-for="i in 3" :key="i" class="h-10 rounded-lg bg-gray-100 dark:bg-gray-800"></div>
            </div>
        </div>

        <!-- FORMULARIO -->
        <form v-else @submit.prevent="submit"
            class="w-full max-w-7xl space-y-6 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:p-6 lg:p-7">

            <!-- ========================= -->
            <!-- DÍAS Y HORAS -->
            <!-- ========================= -->
            <section>
                <div class="mb-4">
                    <h2 class="font-display text-base font-semibold text-gray-900 dark:text-gray-100">
                        Días y horas
                    </h2>

                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                        Define los días y el horario disponible para recibir bicicletas.
                    </p>
                </div>

                <div class="grid gap-2 lg:grid-cols-2 lg:gap-3">

                    <!-- DÍA -->
                    <div v-for="day in days" :key="day.key"
                        class="grid min-w-0 gap-3 rounded-xl border border-gray-200 p-3 transition-colors dark:border-gray-700 sm:p-4 md:grid-cols-[150px_minmax(0,1fr)] md:items-center">

                        <!-- CHECKBOX + DÍA -->
                        <label
                            class="flex min-w-0 cursor-pointer items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            <input type="checkbox" :checked="hours[day.key] !== null"
                                class="h-4 w-4 shrink-0 rounded border-gray-300 text-brand-primary focus:ring-brand-primary dark:border-gray-600 dark:bg-gray-800"
                                @change="
                                    toggleDay(
                                        day.key,
                                        ($event.target as HTMLInputElement).checked
                                    )
                                    " />

                            <span class="truncate">
                                {{ day.label }}
                            </span>
                        </label>

                        <!-- HORARIO -->
                        <div class="min-w-0">

                            <!-- SI ESTÁ ABIERTO -->
                            <div v-if="hours[day.key]" class="flex min-w-0 flex-wrap items-center gap-2 sm:gap-3">
                                <!-- HORA APERTURA -->
                                <input v-model="hours[day.key]!.open" type="time"
                                    class="h-10 w-full min-w-0 rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-900 outline-none transition focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 sm:w-auto sm:min-w-[115px]" />

                                <span class="hidden text-sm text-gray-400 sm:inline">
                                    a
                                </span>

                                <span class="text-xs text-gray-400 sm:hidden">
                                    hasta
                                </span>

                                <!-- HORA CIERRE -->
                                <input v-model="hours[day.key]!.close" type="time"
                                    class="h-10 w-full min-w-0 rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-900 outline-none transition focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 sm:w-auto sm:min-w-[115px]" />
                            </div>

                            <!-- CERRADO -->
                            <span v-else class="inline-flex h-10 items-center text-sm text-gray-400 dark:text-gray-500">
                                Cerrado
                            </span>

                        </div>
                    </div>
                </div>

                <!-- ERROR -->
                <p v-if="getError('business_hours')" class="mt-2 text-xs text-red-600 dark:text-red-400">
                    {{ getError('business_hours') }}
                </p>
            </section>

            <!-- ========================= -->
            <!-- CONFIGURACIÓN -->
            <!-- ========================= -->
            <section class="border-t border-gray-200 pt-6 dark:border-gray-800">

                <div class="mb-5">
                    <h2 class="font-display text-base font-semibold text-gray-900 dark:text-gray-100">
                        Configuración de reservas
                    </h2>

                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                        Configura la capacidad y las condiciones para reservar una cita.
                    </p>
                </div>

                <!-- GRID RESPONSIVO -->
                <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">

                    <!-- CAPACIDAD -->
                    <div class="min-w-0">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            Capacidad simultánea
                        </label>

                        <input v-model.number="capacity" type="number" min="1" required
                            class="mt-1.5 h-11 w-full rounded-lg border px-3 text-sm text-gray-900 outline-none transition focus:ring-2 focus:ring-brand-primary/10 dark:bg-gray-800 dark:text-gray-100"
                            :class="getError('capacity')
                                ? 'border-red-400 dark:border-red-700'
                                : 'border-gray-300 focus:border-brand-primary dark:border-gray-700'
                                " />

                        <p v-if="getError('capacity')" class="mt-1 text-xs text-red-600 dark:text-red-400">
                            {{ getError('capacity') }}
                        </p>

                        <p class="mt-1.5 text-xs leading-5 text-gray-400 dark:text-gray-500">
                            Cuántas bicicletas puedes atender al mismo tiempo
                            (número de mecánicos/espacios).
                        </p>
                    </div>

                    <!-- INTERVALO -->
                    <div class="min-w-0">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            Intervalo entre horarios (minutos)
                        </label>

                        <input v-model.number="slotIntervalMinutes" type="number" min="5" step="5" required
                            class="mt-1.5 h-11 w-full rounded-lg border px-3 text-sm text-gray-900 outline-none transition focus:ring-2 focus:ring-brand-primary/10 dark:bg-gray-800 dark:text-gray-100"
                            :class="getError('slot_interval_minutes')
                                ? 'border-red-400 dark:border-red-700'
                                : 'border-gray-300 focus:border-brand-primary dark:border-gray-700'
                                " />

                        <p v-if="getError('slot_interval_minutes')" class="mt-1 text-xs text-red-600 dark:text-red-400">
                            {{ getError('slot_interval_minutes') }}
                        </p>

                        <p class="mt-1.5 text-xs leading-5 text-gray-400 dark:text-gray-500">
                            Ej: cada 30 minutos aparece un horario nuevo para elegir.
                        </p>
                    </div>

                    <!-- ANTICIPACIÓN -->
                    <div class="min-w-0">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            Días de anticipación permitidos
                        </label>

                        <input v-model.number="advanceBookingDays" type="number" min="1" required
                            class="mt-1.5 h-11 w-full rounded-lg border px-3 text-sm text-gray-900 outline-none transition focus:ring-2 focus:ring-brand-primary/10 dark:bg-gray-800 dark:text-gray-100"
                            :class="getError('advance_booking_days')
                                ? 'border-red-400 dark:border-red-700'
                                : 'border-gray-300 focus:border-brand-primary dark:border-gray-700'
                                " />

                        <p v-if="getError('advance_booking_days')" class="mt-1 text-xs text-red-600 dark:text-red-400">
                            {{ getError('advance_booking_days') }}
                        </p>

                        <p class="mt-1.5 text-xs leading-5 text-gray-400 dark:text-gray-500">
                            Hasta cuántos días a futuro puede reservar un cliente.
                        </p>
                    </div>

                    <!-- AVISO MÍNIMO -->
                    <div class="min-w-0">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            Aviso mínimo (horas)
                        </label>

                        <input v-model.number="minNoticeHours" type="number" min="0" required
                            class="mt-1.5 h-11 w-full rounded-lg border px-3 text-sm text-gray-900 outline-none transition focus:ring-2 focus:ring-brand-primary/10 dark:bg-gray-800 dark:text-gray-100"
                            :class="getError('min_notice_hours')
                                ? 'border-red-400 dark:border-red-700'
                                : 'border-gray-300 focus:border-brand-primary dark:border-gray-700'
                                " />

                        <p v-if="getError('min_notice_hours')" class="mt-1 text-xs text-red-600 dark:text-red-400">
                            {{ getError('min_notice_hours') }}
                        </p>

                        <p class="mt-1.5 text-xs leading-5 text-gray-400 dark:text-gray-500">
                            No se pueden reservar horarios más cercanos a esto desde ahora.
                        </p>
                    </div>

                </div>
            </section>

            <!-- ========================= -->
            <!-- BOTÓN -->
            <!-- ========================= -->
            <div
                class="flex flex-col gap-3 border-t border-gray-200 pt-6 dark:border-gray-800 sm:flex-row sm:justify-end">
                <button type="submit" :disabled="saving"
                    class="inline-flex h-11 w-full items-center justify-center rounded-lg bg-brand-primary px-6 text-sm font-semibold text-white transition hover:brightness-110 disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto">
                    {{ saving ? 'Guardando...' : 'Guardar cambios' }}
                </button>
            </div>

        </form>
    </div>
</template>