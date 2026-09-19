import { api } from "../api";
import type { Category, PaginatedResponse } from "@/types/catalog";

export interface CategoryFilters {
  search?: string;
  status?: "active" | "inactive";
  page?: number;
}

export const adminCategoriesService = {
  list: (filters: CategoryFilters = {}) =>
    api
      .get<
        PaginatedResponse<Category>
      >("/admin/categories", { params: filters })
      .then((r) => r.data),

  create: (formData: FormData) =>
    api
      .post<{ data: Category }>("/admin/categories", formData)
      .then((r) => r.data.data),

  update: (id: number, formData: FormData) => {
    formData.append("_method", "PUT");
    return api
      .post<{ data: Category }>(`/admin/categories/${id}`, formData)
      .then((r) => r.data.data);
  },

  remove: (id: number) => api.delete(`/admin/categories/${id}`),
};
