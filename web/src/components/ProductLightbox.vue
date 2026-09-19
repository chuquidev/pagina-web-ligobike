<script setup lang="ts">
import { ref, watch, onMounted, onUnmounted } from 'vue'
import { X, ChevronLeft, ChevronRight, ZoomIn, ZoomOut } from '@lucide/vue'
import type { ProductImage } from '@/types/catalog'

const props = defineProps<{ images: ProductImage[]; modelValue: number; alt: string }>()
const emit = defineEmits<{ 'update:modelValue': [number]; close: [] }>()

const index = ref(props.modelValue)
const zoomed = ref(false)

watch(
    () => props.modelValue,
    (v) => (index.value = v)
)

function go(delta: number) {
    zoomed.value = false
    index.value = (index.value + delta + props.images.length) % props.images.length
    emit('update:modelValue', index.value)
}

function toggleZoom() {
    zoomed.value = !zoomed.value
}

function onKeydown(e: KeyboardEvent) {
    if (e.key === 'Escape') emit('close')
    if (e.key === 'ArrowLeft') go(-1)
    if (e.key === 'ArrowRight') go(1)
}

onMounted(() => {
    window.addEventListener('keydown', onKeydown)
    document.body.style.overflow = 'hidden'
})
onUnmounted(() => {
    window.removeEventListener('keydown', onKeydown)
    document.body.style.overflow = ''
})
</script>

<template>
    <Teleport to="body">
        <div class="fixed inset-0 z-[100] flex flex-col bg-black/95" @click.self="emit('close')">
            <div class="flex items-center justify-end gap-2 p-3 sm:p-4">
                <button
                    class="flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-white hover:bg-white/20"
                    :aria-label="zoomed ? 'Alejar' : 'Acercar'" @click="toggleZoom">
                    <ZoomOut v-if="zoomed" class="h-5 w-5" />
                    <ZoomIn v-else class="h-5 w-5" />
                </button>
                <button
                    class="flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-white hover:bg-white/20"
                    aria-label="Cerrar" @click="emit('close')">
                    <X class="h-5 w-5" />
                </button>
            </div>

            <div class="relative flex flex-1 items-center justify-center overflow-hidden px-4 pb-4"
                @click.self="emit('close')">
                <img :src="images[index].large" :alt="alt"
                    class="max-h-full max-w-full cursor-zoom-in object-contain transition-transform duration-300"
                    :class="zoomed ? 'scale-150 cursor-zoom-out sm:scale-[1.8]' : ''" @click="toggleZoom" />

                <button v-if="images.length > 1"
                    class="absolute left-2 top-1/2 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-white/10 text-white hover:bg-white/20 sm:left-4"
                    aria-label="Anterior" @click.stop="go(-1)">
                    <ChevronLeft class="h-6 w-6" />
                </button>
                <button v-if="images.length > 1"
                    class="absolute right-2 top-1/2 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-white/10 text-white hover:bg-white/20 sm:right-4"
                    aria-label="Siguiente" @click.stop="go(1)">
                    <ChevronRight class="h-6 w-6" />
                </button>
            </div>

            <p v-if="images.length > 1" class="pb-4 text-center text-sm text-white/60">{{ index + 1 }} / {{
                images.length }}</p>
        </div>
    </Teleport>
</template>