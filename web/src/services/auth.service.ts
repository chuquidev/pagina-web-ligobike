import { api, ensureCsrfCookie } from "./api";

export interface AdminUser {
  id: number;
  name: string;
  email: string;
}

export const authService = {
  async login(email: string, password: string) {
    await ensureCsrfCookie();
    const { data } = await api.post<{ user: AdminUser }>("/admin/login", {
      email,
      password,
    });
    return data.user;
  },
  async logout() {
    await api.post("/admin/logout");
  },
  async me() {
    const { data } = await api.get<AdminUser>("/admin/me");
    return data;
  },
};
