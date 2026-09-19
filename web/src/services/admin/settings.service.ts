import { api } from "../api";
import type { StoreSettings } from "@/types/catalog";

export const adminSettingsService = {
  update: (formData: FormData) => {
    formData.append("_method", "PUT");
    return api
      .post<{ data: StoreSettings }>("/admin/settings", formData)
      .then((r) => r.data.data);
  },

  deleteAboutImage: (mediaId: number) =>
    api.delete(`/admin/settings/about-images/${mediaId}`),
};
