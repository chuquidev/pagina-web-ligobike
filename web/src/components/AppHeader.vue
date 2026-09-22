<script setup lang="ts">
import { ref, watch } from "vue";
import {
  ShoppingBag,
  ShoppingCart,
  Menu,
  X,
  Search,
  Home,
  LayoutGrid,
  Users,
  Wrench,
  ChevronRight,
} from "@lucide/vue";
import { useSettingsStore } from "@/stores/settings";
import { useCartStore } from "@/stores/cart";
import ThemeToggle from "@/components/ThemeToggle.vue";
import CartDrawer from "@/components/CartDrawer.vue";
import AppSearchBox from "@/components/AppSearchBox.vue";

const settingsStore = useSettingsStore();
const cartStore = useCartStore();
const mobileMenuOpen = ref(false);
const mobileSearchOpen = ref(false);
const cartOpen = ref(false);
const bump = ref(false);

watch(
  () => cartStore.totalItems,
  (newVal, oldVal) => {
    if (newVal > oldVal) {
      bump.value = true;
      setTimeout(() => (bump.value = false), 350);
    }
  },
);

// El panel del menú móvil es un overlay, así que mientras está abierto
// bloqueamos el scroll del body (si no, se puede hacer scroll "detrás"
// del overlay en iOS/Android).
watch(mobileMenuOpen, (open) => {
  document.body.style.overflow = open ? "hidden" : "";
});

const navItems = [
  { to: "/", label: "Inicio", icon: Home, exact: true },
  { to: "/catalogo", label: "Catálogo", icon: LayoutGrid, exact: false },
  { to: "/nosotros", label: "Nosotros", icon: Users, exact: false },
  {
    to: "/reservar-mantenimiento",
    label: "Mantenimiento",
    icon: Wrench,
    exact: false,
  },
];
const desktopNavItems = navItems;
const mobileNavItems = navItems;
</script>

<template>
  <header
    class="sticky top-0 z-40 border-b border-gray-200 bg-white/90 backdrop-blur dark:border-gray-800 dark:bg-gray-950/90"
  >
    <div
      class="mx-auto flex max-w-[1400px] items-center gap-3 px-4 py-3 lg:gap-4"
    >
      <RouterLink
        to="/"
        class="flex shrink-0 items-center gap-2.5 font-display font-bold text-brand-primary"
      >
        <img
          v-if="settingsStore.settings?.logo"
          :src="settingsStore.settings.logo"
          :alt="settingsStore.settings.store_name"
          class="h-12 w-12 shrink-0 rounded-lg object-contain"
        />
        <ShoppingBag v-else class="h-10 w-10 shrink-0" />
        <span class="hidden truncate text-lg tracking-tight sm:inline">{{
          settingsStore.settings?.store_name ?? "Cargando..."
        }}</span>
      </RouterLink>

      <!-- Nav de escritorio: recién a partir de lg (1024px) — antes de eso queda apretado
                 junto al buscador, así que tablets usan el menú hamburguesa, más cómodo al tacto. -->
      <nav class="hidden shrink-0 items-center gap-1 lg:ml-3 lg:flex">
        <RouterLink
          v-for="item in desktopNavItems"
          :key="item.to"
          :to="item.to"
          custom
          v-slot="{ href, navigate, isExactActive, isActive }"
        >
          <a
            :href="href"
            class="relative whitespace-nowrap rounded-lg px-3.5 py-2 font-display text-sm font-bold tracking-tight transition-colors duration-200"
            :class="
              (item.exact ? isExactActive : isActive)
                ? 'text-brand-primary'
                : 'text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white'
            "
            @click="navigate"
          >
            {{ item.label }}
            <span
              class="absolute inset-x-3 -bottom-0.5 h-0.5 origin-center rounded-full bg-gradient-to-r from-brand-primary to-brand-secondary transition-transform duration-200"
              :class="
                (item.exact ? isExactActive : isActive)
                  ? 'scale-x-100'
                  : 'scale-x-0'
              "
            />
          </a>
        </RouterLink>
      </nav>

      <AppSearchBox class="ml-auto hidden max-w-md flex-1 lg:block" />

      <button
        class="relative hidden shrink-0 rounded-lg p-2 text-gray-600 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800 lg:block"
        aria-label="Ver carrito"
        @click="cartOpen = true"
      >
        <ShoppingCart
          class="h-5 w-5 transition-transform duration-300"
          :class="{ 'scale-125': bump }"
        />
        <span
          v-if="cartStore.totalItems"
          class="absolute -right-1 -top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-brand-primary px-1 text-[10px] font-bold text-white transition-transform duration-300"
          :class="{ 'scale-125': bump }"
        >
          {{ cartStore.totalItems }}
        </span>
      </button>

      <ThemeToggle class="hidden shrink-0 lg:block" />

      <div class="ml-auto flex shrink-0 items-center gap-1 lg:hidden">
        <button
          class="relative rounded-lg p-2 text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
          aria-label="Ver carrito"
          @click="cartOpen = true"
        >
          <ShoppingCart
            class="h-5 w-5 transition-transform duration-300"
            :class="{ 'scale-125': bump }"
          />
          <span
            v-if="cartStore.totalItems"
            class="absolute -right-1 -top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-brand-primary px-1 text-[10px] font-bold text-white transition-transform duration-300"
            :class="{ 'scale-125': bump }"
          >
            {{ cartStore.totalItems }}
          </span>
        </button>
        <ThemeToggle />
        <button
          class="rounded-lg p-2 text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
          aria-label="Buscar"
          @click="
            mobileSearchOpen = !mobileSearchOpen;
            mobileMenuOpen = false;
          "
        >
          <Search class="h-5 w-5" />
        </button>
        <button
          class="rounded-lg p-2 text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
          aria-label="Abrir menú"
          @click="
            mobileMenuOpen = !mobileMenuOpen;
            mobileSearchOpen = false;
          "
        >
          <component :is="mobileMenuOpen ? X : Menu" class="h-5 w-5" />
        </button>
      </div>
    </div>

    <div
      v-if="mobileSearchOpen"
      class="border-t border-gray-100 px-4 py-3 dark:border-gray-800 lg:hidden"
    >
      <AppSearchBox autofocus @navigated="mobileSearchOpen = false" />
    </div>

    <Teleport to="body">
      <!-- Overlay: difumina/oscurece el resto de la pantalla mientras el drawer está abierto -->
      <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div
          v-if="mobileMenuOpen"
          class="fixed inset-0 z-40 bg-gray-900/60 backdrop-blur-sm lg:hidden"
          @click="mobileMenuOpen = false"
        />
      </Transition>

      <!-- Drawer: anclado a la izquierda (al revés que la referencia, que se abre desde la derecha) -->
      <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="-translate-x-full"
        enter-to-class="translate-x-0"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="translate-x-0"
        leave-to-class="-translate-x-full"
      >
        <nav
          v-if="mobileMenuOpen"
          class="fixed inset-y-0 left-0 z-50 flex h-[100dvh] w-[75%] max-w-sm flex-col overflow-y-auto bg-white text-gray-600 shadow-xl dark:bg-gray-950 dark:text-gray-300 lg:hidden"
        >
          <div
            class="flex shrink-0 items-center justify-between border-b border-gray-100 px-4 py-3 dark:border-gray-800"
          >
            <RouterLink
              to="/"
              class="flex min-w-0 items-center gap-2.5 font-display font-bold text-brand-primary"
              @click="mobileMenuOpen = false"
            >
              <img
                v-if="settingsStore.settings?.logo"
                :src="settingsStore.settings.logo"
                :alt="settingsStore.settings.store_name"
                class="h-9 w-9 shrink-0 rounded-lg object-contain"
              />
              <ShoppingBag v-else class="h-8 w-8 shrink-0" />
              <span class="truncate text-lg tracking-tight">{{
                settingsStore.settings?.store_name ?? "Cargando..."
              }}</span>
            </RouterLink>
            <button
              class="rounded-lg p-2 text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
              aria-label="Cerrar menú"
              @click="mobileMenuOpen = false"
            >
              <X class="h-6 w-6" />
            </button>
          </div>

          <div class="flex flex-1 flex-col overflow-y-auto px-3 py-4">
            <p
              class="px-3 pb-2 font-display text-[11px] font-bold uppercase tracking-[0.2em] text-gray-400 dark:text-gray-500"
            >
              Menú
            </p>
            <div class="flex flex-col gap-1.5">
              <RouterLink
                v-for="(item, i) in mobileNavItems"
                :key="item.to"
                :to="item.to"
                custom
                v-slot="{ href, navigate, isExactActive, isActive }"
              >
                <a
                  :href="href"
                  class="menu-item group relative flex items-center gap-3 overflow-hidden rounded-2xl px-3 py-3 font-display text-base font-bold tracking-tight transition-all duration-200 active:scale-[0.97]"
                  :style="{ animationDelay: `${i * 60}ms` }"
                  :class="
                    (item.exact ? isExactActive : isActive)
                      ? 'text-brand-primary'
                      : 'text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-800'
                  "
                  @click="
                    navigate($event);
                    mobileMenuOpen = false;
                  "
                >
                  <span
                    class="absolute inset-y-2 left-0 w-1 rounded-r-full bg-gradient-to-b from-brand-primary to-brand-secondary transition-opacity"
                    :class="
                      (item.exact ? isExactActive : isActive)
                        ? 'opacity-100'
                        : 'opacity-0'
                    "
                  />
                  <span
                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl transition-all duration-200 group-hover:scale-105"
                    :class="
                      (item.exact ? isExactActive : isActive)
                        ? 'bg-brand-primary/10 text-brand-primary'
                        : 'bg-gray-100 text-gray-500 group-hover:bg-gray-200 group-hover:text-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:group-hover:bg-gray-700 dark:group-hover:text-gray-200'
                    "
                  >
                    <component :is="item.icon" class="h-5 w-5" />
                  </span>
                  <span class="flex-1 truncate">{{ item.label }}</span>
                  <ChevronRight
                    class="h-4 w-4 shrink-0 text-gray-300 transition-transform duration-200 group-hover:translate-x-1 dark:text-gray-600"
                  />
                </a>
              </RouterLink>
            </div>
          </div>

          <div
            class="shrink-0 space-y-3 border-t border-gray-100 px-4 py-4 dark:border-gray-800"
          >
            <RouterLink
              to="/reservar-mantenimiento"
              class="group relative flex items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-brand-primary to-brand-secondary px-4 py-3.5 font-display text-base font-bold tracking-tight text-white shadow-lg shadow-brand-primary/30 transition-transform duration-200 hover:scale-[1.02] active:scale-[0.97]"
              @click="mobileMenuOpen = false"
            >
              <span
                class="pointer-events-none absolute inset-0 -translate-x-full bg-gradient-to-r from-transparent via-white/25 to-transparent transition-transform duration-700 group-hover:translate-x-full"
              />
              <Wrench class="h-4 w-4" />
              Reservar mantenimiento
              <ChevronRight class="h-4 w-4" />
            </RouterLink>
            <p
              v-if="settingsStore.settings?.store_name"
              class="text-center text-xs text-gray-400 dark:text-gray-500"
            >
              {{ settingsStore.settings.store_name
              }}<template v-if="settingsStore.settings?.address">
                · {{ settingsStore.settings.address }}</template
              >
            </p>
          </div>
        </nav>
      </Transition>
    </Teleport>

    <CartDrawer v-if="cartOpen" @close="cartOpen = false" />
  </header>
</template>

<style scoped>
/* Entrada escalonada de los links del drawer: cada uno se desliza y aparece
   con un pequeño retraso (animation-delay inline por índice), en vez de
   aparecer todos de golpe. Se repite cada vez que el drawer se abre porque
   el v-if lo desmonta/remonta por completo. */
.menu-item {
  opacity: 0;
  animation: menu-item-in 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes menu-item-in {
  from {
    opacity: 0;
    transform: translateX(-12px);
  }

  to {
    opacity: 1;
    transform: translateX(0);
  }
}
</style>
