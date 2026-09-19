import { defineStore } from "pinia";

type Section = "public" | "admin";

function readStoredPreference(section: Section): boolean {
  const stored = localStorage.getItem(`theme-${section}`);
  if (stored) return stored === "dark";
  return window.matchMedia("(prefers-color-scheme: dark)").matches;
}

export const useThemeStore = defineStore("theme", {
  state: () => ({
    section: (window.location.pathname.startsWith("/admin")
      ? "admin"
      : "public") as Section,
    isDark: document.documentElement.classList.contains("dark"),
  }),
  actions: {
    setSection(section: Section) {
      if (this.section === section) return;
      this.section = section;
      this.isDark = readStoredPreference(section);
      document.documentElement.classList.toggle("dark", this.isDark);
    },
    toggle() {
      this.isDark = !this.isDark;
      document.documentElement.classList.toggle("dark", this.isDark);
      localStorage.setItem(
        `theme-${this.section}`,
        this.isDark ? "dark" : "light",
      );
    },
  },
});
