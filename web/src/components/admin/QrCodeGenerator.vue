<script setup lang="ts">
import { ref } from 'vue'
import { QrcodeCanvas } from 'qrcode.vue'
import { Download } from '@lucide/vue'

const props = defineProps<{ defaultValue: string }>()
const value = ref(props.defaultValue)
const qrRef = ref<{ download: (filename?: string) => void } | null>(null)

function download() {
    qrRef.value?.download('codigo-qr-catalogo.png')
}
</script>

<template>
    <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900 sm:p-6">
        <h2 class="font-display text-base font-semibold text-gray-900 dark:text-gray-100">Código QR</h2>
        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
            Genera un QR para imprimir en tu local o incluir en publicidad. Puedes apuntarlo a tu inicio, al catálogo, o
            a un producto específico.
        </p>

        <div class="mt-4">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Enlace</label>
            <input v-model="value" type="url"
                class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-primary focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100" />
        </div>

        <div class="mt-4 flex flex-col items-center gap-4 sm:flex-row">
            <div class="rounded-xl border border-gray-200 p-3 dark:border-gray-700">
                <QrcodeCanvas ref="qrRef" :value="value" :size="180" level="M" />
            </div>
            <button type="button"
                class="flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                @click="download">
                <Download class="h-4 w-4" />
                Descargar PNG
            </button>
        </div>
    </div>
</template>