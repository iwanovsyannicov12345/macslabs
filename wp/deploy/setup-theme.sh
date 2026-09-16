#!/usr/bin/env bash
# ============================================================================
#  Ставит тему mac:lab и нужные плагины. Запускать после install.sh, от root.
#  Ожидает, что maclab.zip лежит рядом со скриптом.
# ============================================================================
set -euo pipefail
ROOT="/var/www/maclab"
ZIP="${1:-$(dirname "$0")/maclab.zip}"

[ -f "$ZIP" ] || { echo "Не найден $ZIP"; exit 1; }
cp "$ZIP" "$ROOT/maclab.zip"
chown www-data:www-data "$ROOT/maclab.zip"

sudo -u www-data -s <<'EOSU'
set -e
cd /var/www/maclab

# --- тема ---
wp theme install maclab.zip --force --activate
rm -f maclab.zip

# --- главная страница ---
if ! wp post list --post_type=page --name=glavnaya --format=ids | grep -q .; then
  PAGE_ID=$(wp post create --post_type=page --post_title='Главная' --post_status=publish --porcelain)
else
  PAGE_ID=$(wp post list --post_type=page --name=glavnaya --format=ids)
fi
wp option update show_on_front page
wp option update page_on_front "$PAGE_ID"

# --- плагины ---
wp plugin install wordpress-seo wp-mail-smtp wp-super-cache antispam-bee updraftplus --activate

wp cache flush || true
echo "Тема и плагины установлены."
EOSU

echo "Готово. Проверьте сайт и раздел «mac:lab» в админке."
