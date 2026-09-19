import { createRouter, createWebHistory } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import { useThemeStore } from "@/stores/theme";
import { trackPageView } from "@/utils/analytics";

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: "/",
      component: () => import("@/layouts/PublicLayout.vue"),
      children: [
        {
          path: "",
          name: "home",
          component: () => import("@/views/HomeView.vue"),
        },
        {
          path: "catalogo",
          name: "catalog",
          component: () => import("@/views/CatalogView.vue"),
        },
        {
          path: "producto/:slug",
          name: "product",
          component: () => import("@/views/ProductView.vue"),
          props: true,
        },
        {
          path: "nosotros",
          name: "about",
          component: () => import("@/views/AboutView.vue"),
        },
        {
          path: "preguntas-frecuentes",
          name: "faq",
          component: () => import("@/views/FaqView.vue"),
        },
        {
          path: "guia-tallas",
          name: "size-guide",
          component: () => import("@/views/SizeGuideView.vue"),
        },
        {
          path: "reservar-mantenimiento",
          name: "maintenance-booking",
          component: () => import("@/views/MaintenanceBookingView.vue"),
        },
        {
          path: "politica-privacidad",
          name: "privacy-policy",
          component: () => import("@/views/LegalPageView.vue"),
          props: { field: "privacy_policy", title: "Política de privacidad" },
        },
        {
          path: "terminos-condiciones",
          name: "terms-conditions",
          component: () => import("@/views/LegalPageView.vue"),
          props: { field: "terms_conditions", title: "Términos y condiciones" },
        },
      ],
    },
    {
      path: "/admin/login",
      name: "admin-login",
      component: () => import("@/views/admin/LoginView.vue"),
    },
    {
      path: "/admin",
      component: () => import("@/layouts/AdminLayout.vue"),
      meta: { requiresAuth: true },
      children: [
        { path: "", redirect: { name: "admin-dashboard" } },
        {
          path: "dashboard",
          name: "admin-dashboard",
          component: () => import("@/views/admin/DashboardView.vue"),
        },
        {
          path: "products",
          name: "admin-products",
          component: () => import("@/views/admin/ProductsView.vue"),
        },
        {
          path: "categories",
          name: "admin-categories",
          component: () => import("@/views/admin/CategoriesView.vue"),
        },
        {
          path: "brands",
          name: "admin-brands",
          component: () => import("@/views/admin/BrandsView.vue"),
        },
        {
          path: "banners",
          name: "admin-banners",
          component: () => import("@/views/admin/BannersView.vue"),
        },
        {
          path: "maintenance-services",
          name: "admin-maintenance-services",
          component: () => import("@/views/admin/MaintenanceServicesView.vue"),
        },
        {
          path: "maintenance-schedule",
          name: "admin-maintenance-schedule",
          component: () => import("@/views/admin/MaintenanceSettingsView.vue"),
        },
        {
          path: "appointments",
          name: "admin-appointments",
          component: () =>
            import("@/views/admin/MaintenanceAppointmentsView.vue"),
        },
        {
          path: "faqs",
          name: "admin-faqs",
          component: () => import("@/views/admin/FaqsView.vue"),
        },
        {
          path: "settings",
          name: "admin-settings",
          component: () => import("@/views/admin/SettingsView.vue"),
        },
      ],
    },
    {
      path: "/:pathMatch(.*)*",
      component: () => import("@/layouts/PublicLayout.vue"),
      children: [
        {
          path: "",
          name: "not-found",
          component: () => import("@/views/NotFoundView.vue"),
        },
      ],
    },
  ],
  scrollBehavior: () => ({ top: 0 }),
});

router.beforeEach(async (to) => {
  if (!to.meta.requiresAuth) return true;

  const authStore = useAuthStore();
  if (!authStore.checked) {
    await authStore.fetchUser();
  }

  if (!authStore.isAuthenticated) {
    return { name: "admin-login", query: { redirect: to.fullPath } };
  }

  return true;
});

router.afterEach((to) => {
  const themeStore = useThemeStore();
  themeStore.setSection(to.path.startsWith("/admin") ? "admin" : "public");
});

router.afterEach((to) => {
  const isAdmin = to.path.startsWith("/admin");
  const existingLink = document.querySelector('link[rel="manifest"]');

  if (isAdmin && !existingLink) {
    const link = document.createElement("link");
    link.rel = "manifest";
    link.href = "/manifest.webmanifest";
    document.head.appendChild(link);
  } else if (!isAdmin && existingLink) {
    existingLink.remove();
  }
});

let navigationCount = 0;
router.afterEach(() => {
  navigationCount++;
});

router.afterEach((to) => {
  if (to.path.startsWith("/admin")) return;
  trackPageView(
    to.fullPath,
    typeof to.meta.title === "string" ? to.meta.title : document.title,
  );
});

export function canGoBack() {
  return navigationCount > 1;
}

export default router;
