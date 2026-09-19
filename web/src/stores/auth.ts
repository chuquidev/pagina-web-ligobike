import { defineStore } from "pinia";
import { authService, type AdminUser } from "@/services/auth.service";

export const useAuthStore = defineStore("auth", {
  state: () => ({
    user: null as AdminUser | null,
    checked: false,
  }),
  getters: {
    isAuthenticated: (state) => !!state.user,
  },
  actions: {
    async login(email: string, password: string) {
      this.user = await authService.login(email, password);
    },
    async logout() {
      await authService.logout();
      this.user = null;
    },
    async fetchUser() {
      try {
        this.user = await authService.me();
      } catch {
        this.user = null;
      } finally {
        this.checked = true;
      }
    },
  },
});
