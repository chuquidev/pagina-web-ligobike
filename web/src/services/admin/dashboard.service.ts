import { api } from "../api";

export interface DashboardStats {
  total_products: number;
  total_categories: number;
  total_brands: number;
  featured_products: number;
  active_products: number;
  inactive_products: number;
  products_with_offers: number;
  products_without_images: number;
  availability_breakdown: {
    in_stock: number;
    out_of_stock: number;
    on_request: number;
  };
  products_by_category: { name: string; count: number }[];
  recent_products: {
    id: number;
    name: string;
    category: string;
    price: string;
    is_active: boolean;
    created_at: string;
    thumb: string | null;
  }[];
  low_stock_products: {
    id: number;
    name: string;
    sku: string | null;
    stock: number;
  }[];
  appointments_next_7_days: { label: string; count: number }[];
  upcoming_appointments_count: number;
  next_appointments: {
    id: number;
    customer_name: string;
    service: string;
    starts_at: string;
    status: string;
  }[];
}

export const adminDashboardService = {
  stats: () =>
    api.get<DashboardStats>("/admin/dashboard/stats").then((r) => r.data),
};
