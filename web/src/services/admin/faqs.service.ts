import { api } from "../api";
import type { Faq, PaginatedResponse } from "@/types/catalog";

export interface FaqFilters {
  search?: string;
  status?: "active" | "inactive";
  page?: number;
}

export interface FaqPayload {
  question: string;
  answer: string;
  order?: number;
  is_active: boolean;
}

export const adminFaqsService = {
  list: (filters: FaqFilters = {}) =>
    api
      .get<PaginatedResponse<Faq>>("/admin/faqs", { params: filters })
      .then((r) => r.data),
  create: (payload: FaqPayload) =>
    api.post<{ data: Faq }>("/admin/faqs", payload).then((r) => r.data.data),
  update: (id: number, payload: FaqPayload) =>
    api
      .put<{ data: Faq }>(`/admin/faqs/${id}`, payload)
      .then((r) => r.data.data),
  remove: (id: number) => api.delete(`/admin/faqs/${id}`),
};
