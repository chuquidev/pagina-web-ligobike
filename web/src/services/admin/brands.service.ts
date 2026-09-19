import { api } from "../api";
import type { Brand, PaginatedResponse } from "@/types/catalog";

export interface BrandFilters {
  search?: string;
  status?: "active" | "inactive";
  page?: number;
}

export interface BrandPayload {
  name: string;
  is_active: boolean;
}

export const adminBrandsService = {
  list: (filters: BrandFilters = {}) =>
    api
      .get<PaginatedResponse<Brand>>("/admin/brands", { params: filters })
      .then((r) => r.data),
  create: (payload: BrandPayload) =>
    api
      .post<{ data: Brand }>("/admin/brands", payload)
      .then((r) => r.data.data),
  update: (id: number, payload: BrandPayload) =>
    api
      .put<{ data: Brand }>(`/admin/brands/${id}`, payload)
      .then((r) => r.data.data),
  remove: (id: number) => api.delete(`/admin/brands/${id}`),
};
