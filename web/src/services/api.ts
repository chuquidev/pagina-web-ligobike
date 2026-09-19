import axios from "axios";
import { useToastStore } from "@/stores/toast";

export const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL,
  withCredentials: true,
  withXSRFToken: true,
});

api.interceptors.response.use(
  (response) => response,
  (error) => {
    const status = error.response?.status;
    const url: string = error.config?.url ?? "";
    const isAuthCheck =
      url.includes("/admin/me") || url.includes("/admin/login");

    if (status === 401 && !isAuthCheck) {
      useToastStore().error("Tu sesión expiró. Inicia sesión de nuevo.");
      setTimeout(() => {
        window.location.href = "/admin/login";
      }, 1200);
    }

    return Promise.reject(error);
  },
);

export async function ensureCsrfCookie() {
  await axios.get(`${import.meta.env.VITE_APP_URL}/sanctum/csrf-cookie`, {
    withCredentials: true,
  });
}
