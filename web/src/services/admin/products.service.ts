import { api } from "../api";
import type {
  Product,
  PaginatedResponse,
  ProductImportRow,
  ProductImportSummary,
} from "@/types/catalog";

export interface ProductAdminFilters {
  search?: string;
  category?: number | string;
  brand?: number | string;
  status?: "active" | "inactive";
  page?: number;
}

export const adminProductsService = {
  list: (filters: ProductAdminFilters = {}) =>
    api
      .get<PaginatedResponse<Product>>("/admin/products", { params: filters })
      .then((r) => r.data),

  create: (formData: FormData) =>
    api
      .post<{ data: Product }>("/admin/products", formData)
      .then((r) => r.data.data),

  update: (id: number, formData: FormData) => {
    formData.append("_method", "PUT");
    return api
      .post<{ data: Product }>(`/admin/products/${id}`, formData)
      .then((r) => r.data.data);
  },

  remove: (id: number) => api.delete(`/admin/products/${id}`),
  toggleActive: (id: number) =>
    api
      .patch<{ data: Product }>(`/admin/products/${id}/toggle-active`)
      .then((r) => r.data.data),
  deleteImage: (productId: number, mediaId: number) =>
    api.delete(`/admin/products/${productId}/images/${mediaId}`),

  previewImport: (file: File) => {
    const formData = new FormData();
    formData.append("file", file);
    return api
      .post<{
        rows: ProductImportRow[];
      }>("/admin/products/import/preview", formData)
      .then((r) => r.data.rows);
  },

  commitImport: (rows: ProductImportRow[]) =>
    api
      .post<ProductImportSummary>("/admin/products/import/commit", { rows })
      .then((r) => r.data),
};
