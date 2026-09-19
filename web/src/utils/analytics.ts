// Integración con Google Analytics 4. No hace nada si no configuras
// VITE_GA_MEASUREMENT_ID en el .env — así el sitio funciona igual en desarrollo
// sin ensuciar tus reportes reales con visitas de prueba.

declare global {
  interface Window {
    dataLayer: unknown[];
    gtag: (...args: unknown[]) => void;
    __gaInitialized?: boolean;
  }
}

const GA_ID = import.meta.env.VITE_GA_MEASUREMENT_ID as string | undefined;

function ensureScriptLoaded() {
  if (!GA_ID || window.__gaInitialized) return;
  window.__gaInitialized = true;

  const script = document.createElement("script");
  script.async = true;
  script.src = `https://www.googletagmanager.com/gtag/js?id=${GA_ID}`;
  document.head.appendChild(script);

  window.dataLayer = window.dataLayer || [];
  window.gtag = function () {
    // Usamos 'arguments' (no un array armado a mano) porque es la forma
    // exacta que gtag.js reconoce internamente para procesar comandos.
    // eslint-disable-next-line prefer-rest-params
    window.dataLayer.push(arguments);
  };
  window.gtag("js", new Date());

  // El Modo de Consentimiento de Google bloquea el envío de eventos
  // personalizados si no se le indica explícitamente que hay permiso
  // para medir. No usamos cookies de publicidad, solo de analítica.
  window.gtag("consent", "default", {
    ad_storage: "denied",
    ad_user_data: "denied",
    ad_personalization: "denied",
    analytics_storage: "granted",
  });

  // Enviamos la vista de página nosotros mismos en cada cambio de ruta
  // (es una SPA, gtag por sí solo solo detecta la primera carga).
  window.gtag("config", GA_ID, { send_page_view: false });
}

/**
 * Registra una vista de página en Google Analytics. Se llama en cada
 * cambio de ruta del sitio público (nunca en el panel admin).
 */
export function trackPageView(path: string, title?: string) {
  if (!GA_ID) return;
  ensureScriptLoaded();
  window.gtag("event", "page_view", {
    page_path: path,
    page_title: title,
    page_location: window.location.href,
  });
}

/**
 * Registra una acción de negocio (agregar al carrito, escribir por
 * WhatsApp, reservar mantenimiento). Usa nombres de evento recomendados
 * por GA4 (add_to_cart, generate_lead) cuando aplica, para aprovechar
 * los informes de monetización ya armados de Analytics.
 */
export function trackEvent(name: string, params?: Record<string, unknown>) {
  if (!GA_ID) return;
  ensureScriptLoaded();
  window.gtag("event", name, params);
}
