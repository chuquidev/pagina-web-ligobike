<script setup lang="ts">
import { onMounted, ref } from 'vue'
import {
    ImagePlus, Plus, X as XIcon, Trash2, Store, Share2, MapPin, Palette, Info, Ruler, FileText,
} from '@lucide/vue'
import { adminSettingsService } from '@/services/admin/settings.service'
import { useSettingsStore } from '@/stores/settings'
import { useToastStore } from '@/stores/toast'
import { useFormErrors } from '@/composables/useFormErrors'
import QrCodeGenerator from '@/components/admin/QrCodeGenerator.vue'
import ConfirmDialog from '@/components/admin/ConfirmDialog.vue'
import type { SizeGuideRow, AboutImage } from '@/types/catalog'

const siteUrl = window.location.origin
const settingsStore = useSettingsStore()
const toastStore = useToastStore()
const { getError, parseErrors, clearErrors } = useFormErrors()

const storeName = ref('')
const whatsappNumber = ref('')
const email = ref('')
const facebookUrl = ref('')
const instagramUrl = ref('')
const tiktokUrl = ref('')
const address = ref('')
const schedule = ref('')
const primaryColor = ref('#1e3a8a')
const secondaryColor = ref('#3b82f6')
const privacyPolicy = ref('')
const termsConditions = ref('')
const aboutContent = ref('')
const sizeGuide = ref<SizeGuideRow[]>([])
const newSizeHeight = ref('')
const newSizeSize = ref('')
const existingAboutImages = ref<AboutImage[]>([])
const newAboutImages = ref<File[]>([])
const newAboutImagePreviews = ref<string[]>([])
const newLogo = ref<File | null>(null)
const newLogoPreview = ref<string | null>(null)
const saving = ref(false)
const confirmDeleteImageId = ref<number | null>(null)

function loadFromStore() {
    const s = settingsStore.settings
    if (!s) return
    storeName.value = s.store_name
    whatsappNumber.value = s.whatsapp_number
    email.value = s.email ?? ''
    facebookUrl.value = s.facebook_url ?? ''
    instagramUrl.value = s.instagram_url ?? ''
    tiktokUrl.value = s.tiktok_url ?? ''
    address.value = s.address ?? ''
    schedule.value = s.schedule ?? ''
    primaryColor.value = s.primary_color
    secondaryColor.value = s.secondary_color
    privacyPolicy.value = s.privacy_policy ?? ''
    termsConditions.value = s.terms_conditions ?? ''
    aboutContent.value = s.about_content ?? ''
    sizeGuide.value = s.size_guide ? [...s.size_guide] : []
    existingAboutImages.value = s.about_images ? [...s.about_images] : []
}

function onLogoSelected(event: Event) {
    const file = (event.target as HTMLInputElement).files?.[0]
    if (!file) return
    newLogo.value = file
    newLogoPreview.value = URL.createObjectURL(file)
}

function onAboutImagesSelected(event: Event) {
    const files = Array.from((event.target as HTMLInputElement).files ?? [])
    newAboutImages.value.push(...files)
    newAboutImagePreviews.value.push(...files.map((f) => URL.createObjectURL(f)))
}

function removeNewAboutImage(index: number) {
    newAboutImages.value.splice(index, 1)
    newAboutImagePreviews.value.splice(index, 1)
}

function requestRemoveAboutImage(mediaId: number) {
    confirmDeleteImageId.value = mediaId
}

async function confirmRemoveAboutImage() {
    if (confirmDeleteImageId.value === null) return
    await adminSettingsService.deleteAboutImage(confirmDeleteImageId.value)
    existingAboutImages.value = existingAboutImages.value.filter((img) => img.id !== confirmDeleteImageId.value)
    confirmDeleteImageId.value = null
    toastStore.success('Foto eliminada.')
}

function addSizeRow() {
    if (!newSizeHeight.value.trim() || !newSizeSize.value.trim()) return
    sizeGuide.value.push({ height: newSizeHeight.value.trim(), size: newSizeSize.value.trim() })
    newSizeHeight.value = ''
    newSizeSize.value = ''
}

function removeSizeRow(index: number) {
    sizeGuide.value.splice(index, 1)
}

async function submit() {
    saving.value = true
    clearErrors()
    try {
        const formData = new FormData()
        formData.append('store_name', storeName.value)
        formData.append('whatsapp_number', whatsappNumber.value)
        if (email.value) formData.append('email', email.value)
        if (facebookUrl.value) formData.append('facebook_url', facebookUrl.value)
        if (instagramUrl.value) formData.append('instagram_url', instagramUrl.value)
        if (tiktokUrl.value) formData.append('tiktok_url', tiktokUrl.value)
        if (address.value) formData.append('address', address.value)
        if (schedule.value) formData.append('schedule', schedule.value)
        if (privacyPolicy.value) formData.append('privacy_policy', privacyPolicy.value)
        if (termsConditions.value) formData.append('terms_conditions', termsConditions.value)
        if (aboutContent.value) formData.append('about_content', aboutContent.value)
        formData.append('primary_color', primaryColor.value)
        formData.append('secondary_color', secondaryColor.value)
        if (newLogo.value) formData.append('logo', newLogo.value)
        newAboutImages.value.forEach((file) => formData.append('about_images[]', file))
        sizeGuide.value.forEach((row, i) => {
            formData.append(`size_guide[${i}][height]`, row.height)
            formData.append(`size_guide[${i}][size]`, row.size)
        })

        const updated = await adminSettingsService.update(formData)
        settingsStore.settings = updated
        settingsStore.applyTheme()
        newLogo.value = null
        newLogoPreview.value = null
        newAboutImages.value = []
        newAboutImagePreviews.value = []
        existingAboutImages.value = updated.about_images ?? []
        toastStore.success('Configuración guardada.')
    } catch (err) {
        toastStore.error(parseErrors(err))
    } finally {
        saving.value = false
    }
}

onMounted(async () => {
    await settingsStore.fetch()
    loadFromStore()
})
</script>

<template>
    <div>
        <div>
            <h1 class="font-display text-xl font-bold text-gray-900 dark:text-gray-100 sm:text-2xl">Configuración de
                tienda</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Esta información aparece en tu sitio público.</p>
        </div>

        <form id="settings-form" class="mt-6 space-y-5" @submit.prevent="submit">

            <!-- IDENTIDAD -->
            <section
                class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900 sm:p-6">
                <div class="flex items-center gap-2.5">
                    <Store class="h-5 w-5 text-brand-primary" />
                    <h2 class="font-display text-base font-semibold text-gray-900 dark:text-gray-100">Identidad</h2>
                </div>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Nombre, logo y contacto principal de tu
                    negocio.
                </p>

                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Logo</label>
                    <div class="mt-2 flex flex-wrap items-center gap-4">
                        <div
                            class="flex h-16 w-16 items-center justify-center overflow-hidden rounded-lg border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800">
                            <img v-if="newLogoPreview ?? settingsStore.settings?.logo"
                                :src="newLogoPreview ?? settingsStore.settings?.logo ?? ''"
                                class="h-full w-full object-contain" />
                        </div>
                        <label for="logo-input"
                            class="flex cursor-pointer items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                            <ImagePlus class="h-4 w-4" />
                            Cambiar logo
                        </label>
                        <input id="logo-input" type="file" accept="image/*" class="hidden" @change="onLogoSelected" />
                    </div>
                    <p v-if="getError('logo')" class="mt-1 text-xs text-red-600 dark:text-red-400">{{ getError('logo')
                    }}
                    </p>
                </div>

                <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Nombre de la tienda</label>
                        <input v-model="storeName" type="text" required
                            class="mt-1 w-full rounded-lg border px-3 py-2 text-sm text-gray-900 focus:outline-none dark:bg-gray-800 dark:text-gray-100"
                            :class="getError('store_name') ? 'border-red-400 dark:border-red-700' : 'border-gray-300 focus:border-brand-primary dark:border-gray-700'" />
                        <p v-if="getError('store_name')" class="mt-1 text-xs text-red-600 dark:text-red-400">{{
                            getError('store_name') }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">WhatsApp</label>
                        <input v-model="whatsappNumber" type="text" required placeholder="51987654321"
                            class="mt-1 w-full rounded-lg border px-3 py-2 text-sm text-gray-900 focus:outline-none dark:bg-gray-800 dark:text-gray-100"
                            :class="getError('whatsapp_number') ? 'border-red-400 dark:border-red-700' : 'border-gray-300 focus:border-brand-primary dark:border-gray-700'" />
                        <p v-if="getError('whatsapp_number')" class="mt-1 text-xs text-red-600 dark:text-red-400">{{
                            getError('whatsapp_number') }}</p>
                        <p v-else class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                            El botón de WhatsApp de todo tu catálogo depende de este número.
                        </p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Correo de contacto</label>
                        <input v-model="email" type="email" placeholder="contacto@dolmarbikes.com"
                            class="mt-1 w-full rounded-lg border px-3 py-2 text-sm text-gray-900 focus:outline-none dark:bg-gray-800 dark:text-gray-100"
                            :class="getError('email') ? 'border-red-400 dark:border-red-700' : 'border-gray-300 focus:border-brand-primary dark:border-gray-700'" />
                        <p v-if="getError('email')" class="mt-1 text-xs text-red-600 dark:text-red-400">{{
                            getError('email') }}</p>
                    </div>
                </div>
            </section>

            <!-- REDES SOCIALES -->
            <section
                class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900 sm:p-6">
                <div class="flex items-center gap-2.5">
                    <Share2 class="h-5 w-5 text-brand-primary" />
                    <h2 class="font-display text-base font-semibold text-gray-900 dark:text-gray-100">Redes sociales
                    </h2>
                </div>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Enlaces mostrados en el pie de página del
                    sitio.
                </p>

                <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Facebook (URL)</label>
                        <input v-model="facebookUrl" type="url" placeholder="https://facebook.com/tutienda"
                            class="mt-1 w-full rounded-lg border px-3 py-2 text-sm text-gray-900 focus:outline-none dark:bg-gray-800 dark:text-gray-100"
                            :class="getError('facebook_url') ? 'border-red-400 dark:border-red-700' : 'border-gray-300 focus:border-brand-primary dark:border-gray-700'" />
                        <p v-if="getError('facebook_url')" class="mt-1 text-xs text-red-600 dark:text-red-400">{{
                            getError('facebook_url') }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Instagram (URL)</label>
                        <input v-model="instagramUrl" type="url" placeholder="https://instagram.com/tutienda"
                            class="mt-1 w-full rounded-lg border px-3 py-2 text-sm text-gray-900 focus:outline-none dark:bg-gray-800 dark:text-gray-100"
                            :class="getError('instagram_url') ? 'border-red-400 dark:border-red-700' : 'border-gray-300 focus:border-brand-primary dark:border-gray-700'" />
                        <p v-if="getError('instagram_url')" class="mt-1 text-xs text-red-600 dark:text-red-400">{{
                            getError('instagram_url') }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">TikTok (URL)</label>
                        <input v-model="tiktokUrl" type="url" placeholder="https://tiktok.com/@tutienda"
                            class="mt-1 w-full rounded-lg border px-3 py-2 text-sm text-gray-900 focus:outline-none dark:bg-gray-800 dark:text-gray-100"
                            :class="getError('tiktok_url') ? 'border-red-400 dark:border-red-700' : 'border-gray-300 focus:border-brand-primary dark:border-gray-700'" />
                        <p v-if="getError('tiktok_url')" class="mt-1 text-xs text-red-600 dark:text-red-400">{{
                            getError('tiktok_url') }}</p>
                    </div>
                </div>
            </section>

            <!-- UBICACIÓN Y HORARIO -->
            <section
                class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900 sm:p-6">
                <div class="flex items-center gap-2.5">
                    <MapPin class="h-5 w-5 text-brand-primary" />
                    <h2 class="font-display text-base font-semibold text-gray-900 dark:text-gray-100">Ubicación y
                        horario</h2>
                </div>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Se muestra en el pie de página y en "Nosotros".
                </p>

                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Dirección</label>
                        <input v-model="address" type="text"
                            class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-primary focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100" />
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Horario</label>
                        <input v-model="schedule" type="text" placeholder="Lun a Sáb 9am - 7pm"
                            class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-primary focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100" />
                    </div>
                </div>
            </section>

            <!-- APARIENCIA -->
            <section
                class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900 sm:p-6">
                <div class="flex items-center gap-2.5">
                    <Palette class="h-5 w-5 text-brand-primary" />
                    <h2 class="font-display text-base font-semibold text-gray-900 dark:text-gray-100">Apariencia</h2>
                </div>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Los colores de tu marca en el sitio público y
                    en
                    este panel.</p>

                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Color primario</label>
                        <div class="mt-1 flex items-center gap-2">
                            <input v-model="primaryColor" type="color"
                                class="h-10 w-14 cursor-pointer rounded border border-gray-300 dark:border-gray-700" />
                            <input v-model="primaryColor" type="text"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-primary focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100" />
                        </div>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Color secundario</label>
                        <div class="mt-1 flex items-center gap-2">
                            <input v-model="secondaryColor" type="color"
                                class="h-10 w-14 cursor-pointer rounded border border-gray-300 dark:border-gray-700" />
                            <input v-model="secondaryColor" type="text"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-primary focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100" />
                        </div>
                    </div>
                </div>
            </section>

            <!-- SOBRE NOSOTROS -->
            <section
                class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900 sm:p-6">
                <div class="flex items-center gap-2.5">
                    <Info class="h-5 w-5 text-brand-primary" />
                    <h2 class="font-display text-base font-semibold text-gray-900 dark:text-gray-100">Sobre nosotros
                    </h2>
                </div>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Se muestra en la página pública "Nosotros".</p>

                <div class="mt-4">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Descripción</label>
                    <textarea v-model="aboutContent" rows="6"
                        class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-primary focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"></textarea>
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Separa los párrafos con una línea en blanco
                        entre ellos.</p>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fotos</label>

                    <div v-if="existingAboutImages.length" class="mt-2 flex flex-wrap gap-2">
                        <div v-for="img in existingAboutImages" :key="img.id"
                            class="group relative h-20 w-20 overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
                            <img :src="img.url" class="h-full w-full object-cover" />
                            <button type="button"
                                class="absolute inset-0 flex items-center justify-center bg-black/50 text-white opacity-0 transition group-hover:opacity-100"
                                @click="requestRemoveAboutImage(img.id)">
                                <Trash2 class="h-5 w-5" />
                            </button>
                        </div>
                    </div>

                    <div v-if="newAboutImagePreviews.length" class="mt-2 flex flex-wrap gap-2">
                        <div v-for="(preview, i) in newAboutImagePreviews" :key="preview"
                            class="group relative h-20 w-20 overflow-hidden rounded-lg border-2 border-brand-primary/40">
                            <img :src="preview" class="h-full w-full object-cover" />
                            <button type="button"
                                class="absolute inset-0 flex items-center justify-center bg-black/50 text-white opacity-0 transition group-hover:opacity-100"
                                @click="removeNewAboutImage(i)">
                                <Trash2 class="h-5 w-5" />
                            </button>
                        </div>
                    </div>

                    <label for="about-images-input"
                        class="mt-2 flex cursor-pointer items-center justify-center gap-2 rounded-lg border-2 border-dashed border-gray-300 px-4 py-6 text-sm text-gray-500 transition hover:border-brand-primary hover:text-brand-primary dark:border-gray-700 dark:text-gray-400">
                        <ImagePlus class="h-5 w-5" />
                        Haz clic para subir fotos
                    </label>
                    <input id="about-images-input" type="file" accept="image/*" multiple class="hidden"
                        @change="onAboutImagesSelected" />
                </div>
            </section>

            <!-- GUÍA DE TALLAS -->
            <section
                class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900 sm:p-6">
                <div class="flex items-center gap-2.5">
                    <Ruler class="h-5 w-5 text-brand-primary" />
                    <h2 class="font-display text-base font-semibold text-gray-900 dark:text-gray-100">Guía de tallas
                    </h2>
                </div>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Se muestra en la página pública "Guía de
                    tallas".</p>

                <div v-if="sizeGuide.length"
                    class="mt-4 overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
                    <table class="w-full text-left text-sm">
                        <thead
                            class="bg-gray-50 text-xs font-semibold uppercase text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                            <tr>
                                <th class="px-3 py-2">Estatura</th>
                                <th class="px-3 py-2">Talla</th>
                                <th class="px-3 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <tr v-for="(row, i) in sizeGuide" :key="i">
                                <td class="px-3 py-1.5">
                                    <input v-model="row.height"
                                        class="w-full rounded-md border-0 bg-transparent px-2 py-1.5 text-sm text-gray-700 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-primary dark:text-gray-300 dark:focus:bg-gray-800" />
                                </td>
                                <td class="px-3 py-1.5">
                                    <input v-model="row.size"
                                        class="w-full rounded-md border-0 bg-transparent px-2 py-1.5 text-sm text-gray-700 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-primary dark:text-gray-300 dark:focus:bg-gray-800" />
                                </td>
                                <td class="px-3 py-1.5 text-right">
                                    <button type="button" class="text-gray-400 hover:text-red-500"
                                        @click="removeSizeRow(i)">
                                        <XIcon class="h-4 w-4" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">Haz clic en cualquier celda para editarla
                    directamente. Recuerda "Guardar cambios" al final.</p>

                <div class="mt-3 flex flex-wrap gap-2">
                    <input v-model="newSizeHeight" type="text" placeholder="Ej: 150 - 160 cm"
                        class="flex-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-primary focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100" />
                    <input v-model="newSizeSize" type="text" placeholder="Ej: S (15&quot;)"
                        class="flex-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-primary focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                        @keydown.enter.prevent="addSizeRow" />
                    <button type="button"
                        class="rounded-lg border border-gray-300 px-3 text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                        @click="addSizeRow">
                        <Plus class="h-4 w-4" />
                    </button>
                </div>
            </section>

            <!-- CONTENIDO LEGAL -->
            <section
                class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900 sm:p-6">
                <div class="flex items-center gap-2.5">
                    <FileText class="h-5 w-5 text-brand-primary" />
                    <h2 class="font-display text-base font-semibold text-gray-900 dark:text-gray-100">Contenido legal
                    </h2>
                </div>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Se muestra en las páginas públicas de política
                    de privacidad y términos y condiciones.</p>

                <div class="mt-4 grid gap-4 lg:grid-cols-2">
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Política de
                            privacidad</label>
                        <textarea v-model="privacyPolicy" rows="8"
                            class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-primary focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"></textarea>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Términos y
                            condiciones</label>
                        <textarea v-model="termsConditions" rows="8"
                            class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-primary focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"></textarea>
                    </div>
                </div>
            </section>

            <QrCodeGenerator :default-value="siteUrl" />
        </form>

        <!-- Barra de guardado fija: no hace falta bajar hasta el final -->
        <div class="sticky bottom-4 z-30 mt-6 flex justify-end">
            <div
                class="flex items-center justify-end rounded-2xl border border-gray-200 bg-white/90 p-3 shadow-lg backdrop-blur dark:border-gray-800 dark:bg-gray-900/90">
                <button form="settings-form" type="submit" :disabled="saving"
                    class="w-full rounded-lg bg-brand-primary px-6 py-2.5 text-sm font-semibold text-white hover:brightness-110 disabled:opacity-60 sm:w-auto">
                    {{ saving ? 'Guardando...' : 'Guardar cambios' }}
                </button>
            </div>
        </div>

        <ConfirmDialog v-if="confirmDeleteImageId !== null" title="Eliminar foto"
            message="¿Eliminar esta foto? Esta acción no se puede deshacer." @confirm="confirmRemoveAboutImage"
            @cancel="confirmDeleteImageId = null" />
    </div>
</template>