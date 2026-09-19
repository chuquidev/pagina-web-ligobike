#!/bin/bash
set -e

# --- Configuración: edita esto si algo cambia ---
VPS_USER="root"
VPS_IP="104.248.4.70"
VPS_PATH="/var/www/dolmarbikes-catalogo/web"
LOCAL_WEB_DIR="web"
# --------------------------------------------------

echo "== 1/4: Compilando frontend =="
cd "$LOCAL_WEB_DIR"
npm run build
cd ..

echo "== 2/4: Borrando dist viejo en el servidor =="
ssh "$VPS_USER@$VPS_IP" "rm -rf $VPS_PATH/dist"

echo "== 3/4: Subiendo dist nuevo =="
scp -r "$LOCAL_WEB_DIR/dist" "$VPS_USER@$VPS_IP:$VPS_PATH/"

echo "== 4/4: Corrigiendo permisos =="
ssh "$VPS_USER@$VPS_IP" "
  chown -R www-data:www-data $VPS_PATH/dist &&
  find $VPS_PATH/dist -type d -exec chmod 755 {} \; &&
  find $VPS_PATH/dist -type f -exec chmod 644 {} \;
"

echo "✅ Listo — https://dolmarbike.com actualizado."