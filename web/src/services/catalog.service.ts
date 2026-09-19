import { api } from "./api";
import type {
  Category,
  Product,
  StoreSettings,
  PaginatedResponse,
  Brand,
  Banner,
  Faq,
  MaintenanceAppointment,
  MaintenanceService,
} from "@/types/catalog";

export interface ProductFilters {
  category?: string;
  brand?: string;
  search?: string;
  featured?: boolean;
  sort?: "newest" | "price_asc" | "price_desc" | "name";
  page?: number;
}

export const catalogService = {
  getSettings: () =>
    api.get<{ data: StoreSettings }>("/settings").then((r) => r.data.data),
  getCategories: () =>
    api.get<{ data: Category[] }>("/categories").then((r) => r.data.data),
  getBrands: () =>
    api.get<{ data: Brand[] }>("/brands").then((r) => r.data.data),
  getProducts: (filters: ProductFilters = {}) =>
    api
      .get<PaginatedResponse<Product>>("/products", { params: filters })
      .then((r) => r.data),
  getProductBySlug: (slug: string) =>
    api.get<{ data: Product }>(`/products/${slug}`).then((r) => r.data.data),
  getBanners: () =>
    api.get<{ data: Banner[] }>("/banners").then((r) => r.data.data),
  getFaqs: () => api.get<{ data: Faq[] }>("/faqs").then((r) => r.data.data),

  getMaintenanceServices: () =>
    api
      .get<{ data: MaintenanceService[] }>("/maintenance/services")
      .then((r) => r.data.data),

  getMaintenanceAvailability: (serviceId: number, date: string) =>
    api
      .get<{
        slots: string[];
      }>("/maintenance/availability", { params: { service_id: serviceId, date } })
      .then((r) => r.data.slots),

  createAppointment: (payload: {
    maintenance_service_id: number;
    date: string;
    time: string;
    customer_name: string;
    customer_phone: string;
    bike_info?: string;
  }) =>
    api
      .post<{
        data: MaintenanceAppointment;
      }>("/maintenance/appointments", payload)
      .then((r) => r.data.data),
};
