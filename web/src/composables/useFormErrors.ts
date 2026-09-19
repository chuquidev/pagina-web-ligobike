import { ref } from "vue";

export function useFormErrors() {
  const errors = ref<Record<string, string[]>>({});

  function getError(field: string): string | null {
    return errors.value[field]?.[0] ?? null;
  }

  function parseErrors(err: unknown): string {
    const axiosError = err as {
      response?: {
        status?: number;
        data?: { errors?: Record<string, string[]>; message?: string };
      };
    };
    if (
      axiosError.response?.status === 422 &&
      axiosError.response.data?.errors
    ) {
      errors.value = axiosError.response.data.errors;
      return axiosError.response.data.message ?? "Revisa los campos marcados.";
    }
    errors.value = {};
    return axiosError.response?.data?.message ?? "Ocurrió un error inesperado.";
  }

  function clearErrors() {
    errors.value = {};
  }

  return { errors, getError, parseErrors, clearErrors };
}
