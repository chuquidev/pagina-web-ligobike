#!/bin/bash
set -e

# --- Configuración ---
VPS_USER="root"
VPS_IP="104.248.4.70"
REMOTE_PATH="/var/www/ligobikes"
# ----------------------

# Ligo corre en Docker: el frontend (web/) se compila DENTRO de la imagen de
# nginx (ver docker/nginx/Dockerfile, etapa "web-build"). No basta con subir
# un dist/ nuevo al host — hay que reconstruir esa imagen.

echo "== 1/2: git pull + reconstruir imagen de nginx en el VPS =="
ssh "$VPS_USER@$VPS_IP" "
  cd $REMOTE_PATH &&
  git pull origin main &&
  docker compose build nginx
"

echo "== 2/2: Reiniciando contenedor nginx con la imagen nueva =="
ssh "$VPS_USER@$VPS_IP" "
  cd $REMOTE_PATH &&
  docker compose up -d nginx
"

echo "✅ Listo — https://ligobikes.com actualizado (frontend)."