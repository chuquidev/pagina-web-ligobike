import { defineStore } from "pinia";

export type ToastType = "success" | "error" | "info";

export interface Toast {
  id: number;
  type: ToastType;
  message: string;
}

let nextId = 1;

export const useToastStore = defineStore("toast", {
  state: () => ({
    toasts: [] as Toast[],
  }),
  actions: {
    push(type: ToastType, message: string, duration = 4000) {
      const id = nextId++;
      this.toasts.push({ id, type, message });
      setTimeout(() => this.dismiss(id), duration);
    },
    success(message: string) {
      this.push("success", message);
    },
    error(message: string) {
      this.push("error", message, 5000);
    },
    info(message: string) {
      this.push("info", message);
    },
    dismiss(id: number) {
      this.toasts = this.toasts.filter((t) => t.id !== id);
    },
  },
});
