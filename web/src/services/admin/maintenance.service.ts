import { api } from "../api";
import type {
  MaintenanceService,
  MaintenanceSettings,
  MaintenanceAppointment,
  PaginatedResponse,
  AppointmentStatus,
} from "@/types/catalog";

export interface MaintenanceServiceFilters {
  search?: string;
  status?: "active" | "inactive";
  page?: number;
}

export interface MaintenanceServicePayload {
  name: string;
  description?: string;
  duration_minutes: number;
  price?: string | number | null;
  is_active: boolean;
  order?: number;
}

export const adminMaintenanceServicesService = {
  list: (filters: MaintenanceServiceFilters = {}) =>
    api
      .get<
        PaginatedResponse<MaintenanceService>
      >("/admin/maintenance-services", { params: filters })
      .then((r) => r.data),
  create: (payload: MaintenanceServicePayload) =>
    api
      .post<{
        data: MaintenanceService;
      }>("/admin/maintenance-services", payload)
      .then((r) => r.data.data),
  update: (id: number, payload: MaintenanceServicePayload) =>
    api
      .put<{
        data: MaintenanceService;
      }>(`/admin/maintenance-services/${id}`, payload)
      .then((r) => r.data.data),
  remove: (id: number) => api.delete(`/admin/maintenance-services/${id}`),
};

export const adminMaintenanceSettingsService = {
  get: () =>
    api
      .get<{ data: MaintenanceSettings }>("/admin/maintenance-settings")
      .then((r) => r.data.data),
  update: (payload: MaintenanceSettings) =>
    api
      .put<{
        data: MaintenanceSettings;
      }>("/admin/maintenance-settings", payload)
      .then((r) => r.data.data),
};

export interface AppointmentFilters {
  status?: AppointmentStatus;
  date?: string;
  search?: string;
  page?: number;
}

export const adminMaintenanceAppointmentsService = {
  list: (filters: AppointmentFilters = {}) =>
    api
      .get<
        PaginatedResponse<MaintenanceAppointment>
      >("/admin/maintenance-appointments", { params: filters })
      .then((r) => r.data),
  updateStatus: (id: number, status: AppointmentStatus) =>
    api
      .patch<{
        data: MaintenanceAppointment;
      }>(`/admin/maintenance-appointments/${id}/status`, { status })
      .then((r) => r.data.data),
  remove: (id: number) => api.delete(`/admin/maintenance-appointments/${id}`),
};
