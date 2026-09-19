export interface Category {
  id: number;
  name: string;
  slug: string;
  order: number;
  is_active: boolean;
  image: string | null;
}

export interface Banner {
  id: number;
  title: string | null;
  subtitle: string | null;
  button_text: string | null;
  button_url: string | null;
  order: number;
  is_active: boolean;
  image: string | null;
}

export interface ProductImage {
  id: number;
  thumb: string;
  large: string;
}

export type Availability = "in_stock" | "out_of_stock" | "on_request";

export interface Brand {
  id: number;
  name: string;
  slug: string;
  is_active: boolean;
}

export interface Product {
  id: number;
  sku: string | null;
  name: string;
  slug: string;
  description: string | null;
  features: string[] | null;
  price: string;
  sale_price: string | null;
  stock: number | null;
  min_price: string | null;
  availability: Availability;
  is_featured: boolean;
  is_active: boolean;
  category: Category;
  brand: Brand | null;
  images: ProductImage[];
}

export interface StoreSettings {
  store_name: string;
  whatsapp_number: string;
  email: string | null;
  facebook_url: string | null;
  instagram_url: string | null;
  tiktok_url: string | null;
  address: string | null;
  schedule: string | null;
  privacy_policy: string | null;
  terms_conditions: string | null;
  about_content: string | null;
  size_guide: SizeGuideRow[];
  about_images: AboutImage[];
  primary_color: string;
  secondary_color: string;
  logo: string | null;
}

export interface Faq {
  id: number;
  question: string;
  answer: string;
  order: number;
  is_active: boolean;
}

export interface SizeGuideRow {
  height: string;
  size: string;
}

export interface AboutImage {
  id: number;
  url: string;
}

export interface PaginatedResponse<T> {
  data: T[];
  meta: { current_page: number; last_page: number; total: number };
}

export interface MaintenanceService {
  id: number;
  name: string;
  description: string | null;
  duration_minutes: number;
  price: string | null;
  is_active: boolean;
  order: number;
}

export type DayKey =
  | "monday"
  | "tuesday"
  | "wednesday"
  | "thursday"
  | "friday"
  | "saturday"
  | "sunday";

export interface MaintenanceSettings {
  business_hours: Record<DayKey, { open: string; close: string } | null>;
  capacity: number;
  slot_interval_minutes: number;
  advance_booking_days: number;
  min_notice_hours: number;
}

export type AppointmentStatus =
  | "pending"
  | "confirmed"
  | "cancelled"
  | "completed";

export interface MaintenanceAppointment {
  id: number;
  service: MaintenanceService;
  customer_name: string;
  customer_phone: string;
  bike_info: string | null;
  starts_at: string;
  ends_at: string;
  status: AppointmentStatus;
  created_at: string;
}

export type ImportRowStatus = "new" | "update" | "invalid";

export interface ProductImportRow {
  row: number;
  sku: string;
  name: string;
  brand: string | null;
  category: string | null;
  price: number | null;
  min_price: number | null;
  stock: number;
  category_exists: boolean;
  brand_exists: boolean;
  existing_product_id: number | null;
  status: ImportRowStatus;
  changes?: Record<string, [unknown, unknown]>;
  errors: string[];
}

export interface ProductImportSummary {
  created: number;
  updated: number;
  skipped: number;
  errors: { row: number | null; sku: string | null; errors: string[] }[];
}
