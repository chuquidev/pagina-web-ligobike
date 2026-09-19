#!/bin/bash
set -e

# --- Configuración ---
VPS_USER="root"
VPS_IP="104.248.4.70"
API_PATH="/var/www/dolmarbikes-catalogo/api"
# ----------------------

echo "== Actualizando backend en el VPS =="
ssh "$VPS_USER@$VPS_IP" "
  cd $API_PATH &&
  git pull origin main &&
  composer install --no-dev --optimize-autoloader &&
  php artisan config:clear &&
  php artisan config:cache &&
  php artisan route:cache &&
  php artisan view:cache &&
  chown -R www-data:www-data storage bootstrap/cache &&
  php artisan queue:restart
"

echo "✅ Backend actualizado. Recuerda: si este cambio incluye una migración nueva, corre 'php artisan migrate --force' manualmente en el VPS — no lo hace este script a propósito, por seguridad de tus datos en producción."