import { defineStore } from "pinia";
import { catalogService } from "@/services/catalog.service";
import type { StoreSettings } from "@/types/catalog";

export const useSettingsStore = defineStore("settings", {
  state: () => ({
    settings: null as StoreSettings | null,
    loaded: false,
  }),
  actions: {
    async fetch() {
      if (this.loaded) return;
      this.settings = await catalogService.getSettings();
      this.applyTheme();
      this.loaded = true;
    },
    applyTheme() {
      if (!this.settings) return;
      const root = document.documentElement;
      root.style.setProperty(
        "--color-brand-primary",
        this.settings.primary_color,
      );
      root.style.setProperty(
        "--color-brand-secondary",
        this.settings.secondary_color,
      );
    },
  },
});
