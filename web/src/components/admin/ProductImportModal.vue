<script setup lang="ts">
import { computed, ref } from 'vue'
import { UploadCloud, AlertTriangle, CheckCircle2 } from '@lucide/vue'
import Modal from './Modal.vue'
import { adminProductsService } from '@/services/admin/products.service'
import { useToastStore } from '@/stores/toast'
import type { ProductImportRow, ProductImportSummary } from '@/types/catalog'

const emit = defineEmits<{ close: []; imported: [] }>()
const toastStore = useToastStore()

const step = ref<'upload' | 'preview' | 'done'>('upload')
const analyzing = ref(false)
const importing = ref(false)
const rows = ref<ProductImportRow[]>([])
const selected = ref<Set<number>>(new Set())
const summary = ref<ProductImportSummary | null>(null)
const fileError = ref('')

const validRows = computed(() => rows.value.filter((r) => r.status !== 'invalid'))
const newCount = computed(() => rows.value.filter((r) => r.status === 'new').length)
const updateCount = computed(() => rows.value.filter((r) => r.status === 'update').length)
const invalidCount = computed(() => rows.value.filter((r) => r.status === 'invalid').length)
const selectedCount = computed(() => selected.value.size)

async function onFileSelected(event: Event) {
    const file = (event.target as HTMLInputElement).files?.[0]
    if (!file) return

    analyzing.value = true
    fileError.value = ''
    try {
        rows.value = await adminProductsService.previewImport(file)
        selected.value = new Set(validRows.value.map((r) => r.row))
        step.value = 'preview'
    } catch (err: any) {
        fileError.value = err?.response?.data?.message ?? 'No se pudo leer el archivo.'
    } finally {
        analyzing.value = false
    }
}

function toggleRow(row: number) {
    if (selected.value.has(row)) selected.value.delete(row)
    else selected.value.add(row)
}

function statusLabel(row: ProductImportRow) {
    if (row.status === 'new') return { text: 'Nuevo', class: 'bg-green-50 text-green-700 dark:bg-green-950/40 dark:text-green-400' }
    if (row.status === 'update') return { text: 'Actualiza', class: 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400' }
    return { text: 'Error', class: 'bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-400' }
}

async function confirmImport() {
    const toApply = rows.value.filter((r) => r.status !== 'invalid' && selected.value.has(r.row))
    if (!toApply.length) return

    importing.value = true
    try {
        summary.value = await adminProductsService.commitImport(toApply)
        step.value = 'done'
        emit('imported')
    } catch {
        toastStore.error('No se pudo completar la importación.')
    } finally {
        importing.value = false
    }
}
</script>

<template>
    <Modal title="Importar productos desde Excel" size="lg" @close="emit('close')">
        <div v-if="step === 'upload'" class="space-y-4">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Sube el reporte de productos de tu sistema (columnas CODIGO, NOMBRE, MARCA, CATEGORIA, P. VENTA,
                P. MIN, STOCK). Nada se guarda todavía: primero verás una vista previa.
            </p>
            <label for="import-file-input"
                class="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed border-gray-300 px-4 py-10 text-sm text-gray-500 transition hover:border-brand-primary hover:text-brand-primary dark:border-gray-700 dark:text-gray-400">
                <UploadCloud class="h-8 w-8" />
                <span v-if="analyzing">Analizando archivo...</span>
                <span v-else>Haz clic para elegir el archivo (.xlsx, .xls, .csv)</span>
            </label>
            <input id="import-file-input" type="file" accept=".xlsx,.xls,.csv" class="hidden" :disabled="analyzing"
                @change="onFileSelected" />
            <p v-if="fileError" class="flex items-center gap-1.5 text-sm text-red-600 dark:text-red-400">
                <AlertTriangle class="h-4 w-4" /> {{ fileError }}
            </p>
        </div>

        <div v-else-if="step === 'preview'" class="space-y-4">
            <div class="flex flex-wrap gap-3 text-sm">
                <span
                    class="rounded-full bg-green-50 px-3 py-1 text-green-700 dark:bg-green-950/40 dark:text-green-400">{{
                    newCount }} nuevos</span>
                <span
                    class="rounded-full bg-amber-50 px-3 py-1 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400">{{
                    updateCount }} para actualizar</span>
                <span v-if="invalidCount"
                    class="rounded-full bg-red-50 px-3 py-1 text-red-700 dark:bg-red-950/40 dark:text-red-400">{{
                    invalidCount }} con error (se omiten)</span>
            </div>

            <div class="max-h-96 overflow-auto rounded-2xl border border-gray-200 dark:border-gray-800">
                <table class="w-full min-w-[720px] text-left text-sm">
                    <thead
                        class="sticky top-0 border-b border-gray-200 bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-400">
                        <tr>
                            <th class="px-3 py-2"></th>
                            <th class="px-3 py-2">Código</th>
                            <th class="px-3 py-2">Nombre</th>
                            <th class="px-3 py-2">Marca / Categoría</th>
                            <th class="px-3 py-2">Precio</th>
                            <th class="px-3 py-2">Stock</th>
                            <th class="px-3 py-2">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr v-for="row in rows" :key="row.row" :class="row.status === 'invalid' ? 'opacity-60' : ''">
                            <td class="px-3 py-2">
                                <input type="checkbox" :disabled="row.status === 'invalid'"
                                    :checked="selected.has(row.row)" @change="toggleRow(row.row)"
                                    class="rounded border-gray-300 text-brand-primary dark:border-gray-600 dark:bg-gray-800" />
                            </td>
                            <td class="px-3 py-2 text-gray-700 dark:text-gray-300">{{ row.sku }}</td>
                            <td class="px-3 py-2 text-gray-900 dark:text-gray-100">{{ row.name }}</td>
                            <td class="px-3 py-2 text-gray-500 dark:text-gray-400">
                                {{ row.brand ?? '—' }}<span v-if="row.brand && !row.brand_exists"
                                    class="text-amber-600 dark:text-amber-400"> (nueva)</span>
                                / {{ row.category ?? '—' }}<span v-if="row.category && !row.category_exists"
                                    class="text-amber-600 dark:text-amber-400"> (nueva)</span>
                            </td>
                            <td class="px-3 py-2 text-gray-700 dark:text-gray-300">
                                S/ {{ row.price }}
                                <span v-if="row.changes?.price"
                                    class="block text-xs text-amber-600 dark:text-amber-400">
                                    antes S/ {{ row.changes.price[0] }}
                                </span>
                            </td>
                            <td class="px-3 py-2 text-gray-700 dark:text-gray-300">
                                {{ row.stock }}
                                <span v-if="row.changes?.stock"
                                    class="block text-xs text-amber-600 dark:text-amber-400">
                                    antes {{ row.changes.stock[0] }}
                                </span>
                            </td>
                            <td class="px-3 py-2">
                                <span class="rounded-full px-2.5 py-1 text-xs font-medium"
                                    :class="statusLabel(row).class">
                                    {{ statusLabel(row).text }}
                                </span>
                                <p v-if="row.errors.length" class="mt-1 text-xs text-red-600 dark:text-red-400">
                                    {{ row.errors.join(' ') }}
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ selectedCount }} fila(s) seleccionada(s)</p>
                <div class="flex gap-2">
                    <button type="button"
                        class="rounded-lg px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
                        @click="step = 'upload'">
                        Elegir otro archivo
                    </button>
                    <button type="button" :disabled="!selectedCount || importing"
                        class="rounded-lg bg-brand-primary px-4 py-2 text-sm font-semibold text-white hover:brightness-110 disabled:opacity-60"
                        @click="confirmImport">
                        {{ importing ? 'Importando...' : `Confirmar importación (${selectedCount})` }}
                    </button>
                </div>
            </div>
        </div>

        <div v-else-if="step === 'done' && summary" class="space-y-4 text-center">
            <CheckCircle2 class="mx-auto h-10 w-10 text-green-500" />
            <p class="text-gray-700 dark:text-gray-300">
                {{ summary.created }} producto(s) creado(s), {{ summary.updated }} actualizado(s)<span
                    v-if="summary.skipped"> , {{ summary.skipped }} omitido(s)</span>.
            </p>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Ahora entra a cada producto nuevo y sube sus fotos desde "Editar".
            </p>
            <button type="button"
                class="rounded-lg bg-brand-primary px-4 py-2 text-sm font-semibold text-white hover:brightness-110"
                @click="emit('close')">
                Listo
            </button>
        </div>
    </Modal>
</template>