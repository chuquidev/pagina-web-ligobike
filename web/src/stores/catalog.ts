import { defineStore } from "pinia";
import { catalogService } from "@/services/catalog.service";
import type { Category, Brand, Banner, Product } from "@/types/catalog";

export const useCatalogStore = defineStore("catalog", {
  state: () => ({
    categories: [] as Category[],
    brands: [] as Brand[],
    banners: [] as Banner[],
    featuredProducts: [] as Product[],
    loaded: false,
    error: false,
  }),
  actions: {
    async fetch() {
      if (this.loaded) return;
      this.error = false;
      try {
        const [categories, brands, banners, featured] = await Promise.all([
          catalogService.getCategories(),
          catalogService.getBrands(),
          catalogService.getBanners(),
          catalogService.getProducts({ featured: true }),
        ]);
        this.categories = categories;
        this.brands = brands;
        this.banners = banners;
        this.featuredProducts = featured.data;
        this.loaded = true;
      } catch {
        this.error = true;
      }
    },
  },
});
