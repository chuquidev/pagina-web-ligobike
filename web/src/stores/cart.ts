import { defineStore } from "pinia";
import type { Product } from "@/types/catalog";
import { trackEvent } from "@/utils/analytics";

export interface CartItem {
  product: Product;
  quantity: number;
}

const STORAGE_KEY = "dolmar-cart";

function loadFromStorage(): CartItem[] {
  try {
    const raw = localStorage.getItem(STORAGE_KEY);
    return raw ? JSON.parse(raw) : [];
  } catch {
    return [];
  }
}

export const useCartStore = defineStore("cart", {
  state: () => ({
    items: loadFromStorage() as CartItem[],
  }),
  getters: {
    totalItems: (state) =>
      state.items.reduce((sum, item) => sum + item.quantity, 0),
    totalPrice: (state) =>
      state.items.reduce((sum, item) => {
        const price = Number(item.product.sale_price ?? item.product.price);
        return sum + price * item.quantity;
      }, 0),
  },
  actions: {
    persist() {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(this.items));
    },
    addItem(product: Product, quantity = 1) {
      const existing = this.items.find((i) => i.product.id === product.id);
      if (existing) {
        existing.quantity += quantity;
      } else {
        this.items.push({ product, quantity });
      }
      this.persist();

      const price = Number(product.sale_price ?? product.price);
      trackEvent("add_to_cart", {
        currency: "PEN",
        value: price * quantity,
        items: [
          {
            item_id: String(product.id),
            item_name: product.name,
            item_category: product.category.name,
            price,
            quantity,
          },
        ],
      });
    },
    updateQuantity(productId: number, quantity: number) {
      const item = this.items.find((i) => i.product.id === productId);
      if (!item) return;
      if (quantity <= 0) {
        this.removeItem(productId);
        return;
      }
      item.quantity = quantity;
      this.persist();
    },
    removeItem(productId: number) {
      this.items = this.items.filter((i) => i.product.id !== productId);
      this.persist();
    },
    clear() {
      this.items = [];
      this.persist();
    },
  },
});
