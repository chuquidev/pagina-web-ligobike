<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { ChevronLeft, ChevronRight } from '@lucide/vue'
import type { Banner } from '@/types/catalog'

const props = defineProps<{ banners: Banner[] }>()

const currentIndex = ref(0)
let autoplayTimer: ReturnType<typeof setInterval> | null = null
let touchStartX = 0

function isExternal(url: string) {
    if (!url.startsWith('http')) return false
    return !url.startsWith(window.location.origin)
}

function internalPath(url: string) {
    return url.startsWith(window.location.origin) ? url.slice(window.location.origin.length) || '/' : url
}

function goTo(index: number) {
    currentIndex.value = (index + props.banners.length) % props.banners.length
}
function next() {
    goTo(currentIndex.value + 1)
}
function prev() {
    goTo(currentIndex.value - 1)
}

function startAutoplay() {
    stopAutoplay()
    if (props.banners.length <= 1) return
    autoplayTimer = setInterval(next, 5500)
}
function stopAutoplay() {
    if (autoplayTimer) clearInterval(autoplayTimer)
}

function onTouchStart(e: TouchEvent) {
    touchStartX = e.touches[0].clientX
}
function onTouchEnd(e: TouchEvent) {
    const delta = e.changedTouches[0].clientX - touchStartX
    if (Math.abs(delta) < 50) return
    delta > 0 ? prev() : next()
}

const hasMultiple = computed(() => props.banners.length > 1)

onMounted(startAutoplay)
onUnmounted(stopAutoplay)
</script>

<template>
    <section class="group relative w-full overflow-hidden aspect-[16/7]" @mouseenter="stopAutoplay"
        @mouseleave="startAutoplay" @touchstart="onTouchStart" @touchend="onTouchEnd">
        <div v-for="(banner, index) in banners" :key="banner.id"
            class="absolute inset-0 transition-opacity duration-700"
            :class="index === currentIndex ? 'z-10 opacity-100' : 'z-0 opacity-0'">
            <img v-if="banner.image" :src="banner.image" :alt="banner.title ?? ''" class="h-full w-full object-cover"
                :fetchpriority="index === 0 ? 'high' : 'low'" :loading="index === 0 ? 'eager' : 'lazy'"
                :decoding="index === 0 ? 'sync' : 'async'" />
            <div v-else class="h-full w-full"
                style="background: linear-gradient(135deg, var(--color-brand-primary), var(--color-brand-secondary))">
            </div>

            <div v-if="banner.button_text && banner.button_url" class="absolute inset-0 bg-black/20"></div>

            <div class="absolute inset-0 flex items-center justify-center px-4">
                <template v-if="banner.button_text && banner.button_url">
                    <a v-if="isExternal(banner.button_url)" :href="banner.button_url" target="_blank" rel="noopener"
                        class="inline-flex items-center gap-2 rounded-full bg-white px-6 py-2.5 font-display text-sm
                        font-bold text-gray-900 shadow-lg transition hover:scale-105 hover:bg-gray-100 sm:px-8 sm:py-3.5
                        sm:text-base lg:px-10 lg:py-4 lg:text-lg">
                        {{ banner.button_text }}
                    </a>
                    <RouterLink v-else :to="internalPath(banner.button_url)"
                        class="inline-flex items-center gap-2 rounded-full bg-white px-6 py-2.5 font-display text-sm font-bold text-gray-900 shadow-lg transition hover:scale-105 hover:bg-gray-100 sm:px-8 sm:py-3.5 sm:text-base lg:px-10 lg:py-4 lg:text-lg">
                        {{ banner.button_text }}
                    </RouterLink>
                </template>
            </div>
        </div>

        <template v-if="hasMultiple">
            <button
                class="absolute left-1 top-1/2 z-20 flex h-7 w-7 -translate-y-1/2 items-center justify-center rounded-full bg-black/30 text-white opacity-0 transition hover:bg-black/50 group-hover:opacity-100 sm:left-3 sm:h-9 sm:w-9 lg:left-6 lg:h-10 lg:w-10"
                aria-label="Anterior" @click="prev">
                <ChevronLeft class="h-4 w-4 sm:h-5 sm:w-5" />
            </button>
            <button
                class="absolute right-1 top-1/2 z-20 flex h-7 w-7 -translate-y-1/2 items-center justify-center rounded-full bg-black/30 text-white opacity-0 transition hover:bg-black/50 group-hover:opacity-100 sm:right-3 sm:h-9 sm:w-9 lg:right-6 lg:h-10 lg:w-10"
                aria-label="Siguiente" @click="next">
                <ChevronRight class="h-4 w-4 sm:h-5 sm:w-5" />
            </button>

            <div
                class="absolute bottom-1.5 left-1/2 z-20 flex -translate-x-1/2 gap-1.5 sm:bottom-3 sm:gap-2 lg:bottom-5">
                <button v-for="(banner, index) in banners" :key="banner.id"
                    class="h-1.5 rounded-full transition-all sm:h-2"
                    :class="index === currentIndex ? 'w-5 bg-white sm:w-6' : 'w-1.5 bg-white/50 hover:bg-white/75 sm:w-2'"
                    :aria-label="`Ir al banner ${index + 1}`" @click="goTo(index)"></button>
            </div>
        </template>
    </section>
</template>