#!/bin/bash
set -e

# --- Configuración ---
VPS_USER="root"
VPS_IP="104.248.4.70"
REMOTE_PATH="/var/www/ligobikes"
# ----------------------

# Ligo corre en Docker: el backend (api/) se copia DENTRO de la imagen del
# contenedor "app" al construirla (ver docker/php/Dockerfile). No basta con
# hacer git pull + composer en el host — hay que reconstruir la imagen.

echo "== 1/2: git pull + reconstruir imágenes app y queue en el VPS =="
ssh "$VPS_USER@$VPS_IP" "
  cd $REMOTE_PATH &&
  git pull origin main &&
  docker compose build app queue
"

echo "== 2/2: Reiniciando contenedores y refrescando cachés de Laravel =="
ssh "$VPS_USER@$VPS_IP" "
  cd $REMOTE_PATH &&
  docker compose up -d app queue &&
  docker compose exec -T app php artisan config:cache &&
  docker compose exec -T app php artisan route:cache &&
  docker compose exec -T app php artisan view:cache
"

echo "✅ Backend actualizado. Recuerda: si este cambio incluye una migración nueva, corre 'docker compose exec app php artisan migrate --force' manualmente en el VPS — no lo hace este script a propósito, por seguridad de tus datos en producción."