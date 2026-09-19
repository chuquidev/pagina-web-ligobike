<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import {
    LayoutDashboard, Package, FolderTree, Tags, Image as ImageIcon, HelpCircle, Settings,
    Wrench, Clock, Calendar, LogOut, Menu, X, Store, ChevronsLeft, ChevronsRight,
} from '@lucide/vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useSettingsStore } from '@/stores/settings'
import ThemeToggle from '@/components/ThemeToggle.vue'
import ConfirmDialog from '@/components/admin/ConfirmDialog.vue'

const authStore = useAuthStore()
const settingsStore = useSettingsStore()
const router = useRouter()
const mobileOpen = ref(false)
const confirmLogoutOpen = ref(false)

onMounted(() => settingsStore.fetch())

const navGroups = [
    {
        label: 'Catálogo',
        items: [
            { to: '/admin/products', label: 'Productos', icon: Package },
            { to: '/admin/categories', label: 'Categorías', icon: FolderTree },
            { to: '/admin/brands', label: 'Marcas', icon: Tags },
            { to: '/admin/banners', label: 'Banners', icon: ImageIcon },
        ],
    },
    {
        label: 'Mantenimiento',
        items: [
            { to: '/admin/maintenance-services', label: 'Servicios', icon: Wrench },
            { to: '/admin/maintenance-schedule', label: 'Horarios', icon: Clock },
            { to: '/admin/appointments', label: 'Citas', icon: Calendar },
        ],
    },
    {
        label: 'Contenido',
        items: [{ to: '/admin/faqs', label: 'Preguntas frecuentes', icon: HelpCircle }],
    },
    {
        label: 'Ajustes',
        items: [{ to: '/admin/settings', label: 'Configuración', icon: Settings }],
    },
]

const RAIL_KEY = 'admin-sidebar-rail'

function loadRailPreference(): boolean {
    return localStorage.getItem(RAIL_KEY) === '1'
}

const railCollapsed = ref(loadRailPreference())

function toggleRail() {
    railCollapsed.value = !railCollapsed.value
    localStorage.setItem(RAIL_KEY, railCollapsed.value ? '1' : '0')
}

const userInitial = computed(() => (authStore.user?.name?.trim()?.[0] ?? '?').toUpperCase())

async function confirmLogout() {
    confirmLogoutOpen.value = false
    await authStore.logout()
    router.push({ name: 'admin-login' })
}
</script>

<template>
    <div class="flex h-screen overflow-hidden bg-gray-50 dark:bg-gray-950">
        <div v-if="mobileOpen" class="fixed inset-0 z-30 bg-black/40 lg:hidden" @click="mobileOpen = false"></div>

        <aside
            class="fixed inset-y-0 left-0 z-40 flex w-72 shrink-0 -translate-x-full flex-col border-r border-gray-200 bg-white transition-[transform,width] duration-200 ease-out dark:border-gray-800 dark:bg-gray-900 lg:translate-x-0"
            :class="[mobileOpen ? 'translate-x-0' : '', railCollapsed ? 'lg:w-[76px]' : 'lg:w-72']">
            <!-- Encabezado: logo + nombre + botón de contraer, todo en la misma fila -->
            <div class="flex h-16 shrink-0 items-center gap-2 border-b border-gray-100 px-4 dark:border-gray-800"
                :class="railCollapsed ? 'lg:justify-center lg:px-0' : ''">
                <img v-if="settingsStore.settings?.logo" :src="settingsStore.settings.logo"
                    class="h-8 w-8 shrink-0 rounded-md object-contain" />
                <div v-else
                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-brand-primary font-display text-sm font-bold text-white">
                    {{ (settingsStore.settings?.store_name ?? 'A').charAt(0) }}
                </div>
                <div class="min-w-0 flex-1" :class="railCollapsed ? 'lg:hidden' : ''">
                    <p class="truncate font-display text-sm font-semibold text-gray-900 dark:text-gray-100">{{
                        settingsStore.settings?.store_name ?? 'Admin' }}</p>
                    <p class="truncate text-[11px] text-gray-400 dark:text-gray-500">Panel de administración</p>
                </div>

                <!-- Contraer/expandir: solo escritorio, junto al nombre -->
                <button
                    class="hidden shrink-0 rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:text-gray-500 dark:hover:bg-gray-800 dark:hover:text-gray-300 lg:block"
                    :class="railCollapsed ? '' : 'ml-auto'" @click="toggleRail"
                    :aria-label="railCollapsed ? 'Expandir menú' : 'Contraer menú'"
                    :title="railCollapsed ? 'Expandir menú' : 'Contraer menú'">
                    <ChevronsRight v-if="railCollapsed" class="h-[18px] w-[18px]" />
                    <ChevronsLeft v-else class="h-[18px] w-[18px]" />
                </button>

                <button class="shrink-0 text-gray-400 dark:text-gray-500 lg:hidden" @click="mobileOpen = false"
                    aria-label="Cerrar menú">
                    <X class="h-5 w-5" />
                </button>
            </div>

            <div class="border-b border-gray-100 px-3 py-3 dark:border-gray-800">
                <a href="https://dolmarbike.com" target="_blank" rel="noopener"
                    class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                    :class="railCollapsed ? 'lg:justify-center lg:px-0' : ''"
                    :title="railCollapsed ? 'Ver tienda' : undefined">
                    <Store class="h-[18px] w-[18px] shrink-0" />
                    <span :class="railCollapsed ? 'lg:hidden' : ''">Ver tienda</span>
                </a>
            </div>

            <nav class="flex-1 space-y-5 overflow-y-auto overflow-x-hidden px-3 py-3">
                <RouterLink to="/admin/dashboard"
                    class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
                    active-class="!bg-brand-primary/10 !text-brand-primary dark:!bg-brand-primary/15"
                    :class="railCollapsed ? 'lg:justify-center lg:px-0' : ''"
                    :title="railCollapsed ? 'Dashboard' : undefined" @click="mobileOpen = false">
                    <LayoutDashboard class="h-[18px] w-[18px] shrink-0" />
                    <span :class="railCollapsed ? 'lg:hidden' : ''">Dashboard</span>
                </RouterLink>

                <div v-for="group in navGroups" :key="group.label">
                    <p class="mb-1 px-3 text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500"
                        :class="railCollapsed ? 'lg:hidden' : ''">
                        {{ group.label }}
                    </p>
                    <div v-if="railCollapsed"
                        class="mx-3 mb-1.5 hidden border-t border-gray-100 dark:border-gray-800 lg:block"></div>
                    <div class="space-y-0.5">
                        <RouterLink v-for="item in group.items" :key="item.to" :to="item.to"
                            class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
                            active-class="!bg-brand-primary/10 !text-brand-primary dark:!bg-brand-primary/15"
                            :class="railCollapsed ? 'lg:justify-center lg:px-0' : ''"
                            :title="railCollapsed ? item.label : undefined" @click="mobileOpen = false">
                            <component :is="item.icon" class="h-[18px] w-[18px] shrink-0" />
                            <span class="truncate" :class="railCollapsed ? 'lg:hidden' : ''">{{ item.label }}</span>
                        </RouterLink>
                    </div>
                </div>
            </nav>

            <div class="shrink-0 border-t border-gray-100 p-3 dark:border-gray-800">
                <div class="flex items-center gap-3 rounded-lg px-2 py-2"
                    :class="railCollapsed ? 'lg:justify-center lg:px-0' : ''">
                    <div
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brand-primary font-display text-sm font-bold text-white">
                        {{ userInitial }}
                    </div>
                    <div class="min-w-0 flex-1" :class="railCollapsed ? 'lg:hidden' : ''">
                        <p class="truncate text-sm font-semibold text-gray-900 dark:text-gray-100">{{
                            authStore.user?.name }}</p>
                        <p class="truncate text-xs text-gray-400 dark:text-gray-500">{{ authStore.user?.email }}</p>
                    </div>
                    <button
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:text-gray-500 dark:hover:bg-gray-800 dark:hover:text-gray-300"
                        :class="railCollapsed ? 'lg:hidden' : ''" aria-label="Cerrar sesión" title="Cerrar sesión"
                        @click="confirmLogoutOpen = true">
                        <LogOut class="h-4 w-4" />
                    </button>
                </div>
            </div>
        </aside>

        <div class="flex min-h-0 min-w-0 flex-1 flex-col transition-[padding] duration-200 ease-out"
            :class="railCollapsed ? 'lg:pl-[76px]' : 'lg:pl-72'">
            <header
                class="flex h-16 shrink-0 items-center gap-3 border-b border-gray-200 bg-white px-4 py-3 dark:border-gray-800 dark:bg-gray-900">
                <button class="text-gray-600 dark:text-gray-300 lg:hidden" @click="mobileOpen = true"
                    aria-label="Abrir menú">
                    <Menu class="h-6 w-6" />
                </button>
                <span class="truncate text-sm text-gray-600 dark:text-gray-300">
                    Hola, <span class="font-semibold text-gray-900 dark:text-gray-100">{{ authStore.user?.name }}</span>
                </span>
                <ThemeToggle class="ml-auto" />
            </header>

            <main class="min-h-0 flex-1 overflow-y-auto overflow-x-hidden p-4 sm:p-6 lg:p-8">
                <RouterView />
            </main>
        </div>

        <ConfirmDialog v-if="confirmLogoutOpen" title="Cerrar sesión" message="¿Seguro que quieres cerrar sesión?"
            confirm-label="Cerrar sesión" variant="primary" @confirm="confirmLogout"
            @cancel="confirmLogoutOpen = false" />
    </div>
</template>