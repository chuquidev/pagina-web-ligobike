import { api } from "../api";
import type { Banner, PaginatedResponse } from "@/types/catalog";

export interface BannerFilters {
  search?: string;
  status?: "active" | "inactive";
  page?: number;
}

export const adminBannersService = {
  list: (filters: BannerFilters = {}) =>
    api
      .get<PaginatedResponse<Banner>>("/admin/banners", { params: filters })
      .then((r) => r.data),

  create: (formData: FormData) =>
    api
      .post<{ data: Banner }>("/admin/banners", formData)
      .then((r) => r.data.data),

  update: (id: number, formData: FormData) => {
    formData.append("_method", "PUT");
    return api
      .post<{ data: Banner }>(`/admin/banners/${id}`, formData)
      .then((r) => r.data.data);
  },

  remove: (id: number) => api.delete(`/admin/banners/${id}`),
};
