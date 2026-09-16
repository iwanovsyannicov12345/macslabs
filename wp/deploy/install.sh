#!/usr/bin/env bash
# ============================================================================
#  Установка WordPress с темой mac:lab на чистый Ubuntu 22.04 / 24.04
#  Запускать от root:  bash install.sh
#  Сайт открывается по IP сервера, домен не нужен.
# ============================================================================
set -euo pipefail

DB_NAME="maclab"
DB_USER="maclab"
DB_PASS="$(head -c 18 /dev/urandom | base64 | tr -d '/+=' | head -c 20)"
WP_ADMIN="admin"
WP_PASS="$(head -c 18 /dev/urandom | base64 | tr -d '/+=' | head -c 16)"
WP_EMAIL="${WP_EMAIL:-admin@example.com}"
IP="$(curl -s --max-time 10 ifconfig.me || hostname -I | awk '{print $1}')"
ROOT="/var/www/maclab"

echo "==> Сервер: $IP"

export DEBIAN_FRONTEND=noninteractive
apt-get update -qq
apt-get install -y -qq nginx mariadb-server php-fpm php-mysql php-xml php-curl \
  php-mbstring php-zip php-gd php-intl unzip curl less

PHPVER="$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')"
echo "==> PHP $PHPVER"

# --- база данных ---------------------------------------------------------
mysql -e "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';"
mysql -e "ALTER USER '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';"
mysql -e "GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost'; FLUSH PRIVILEGES;"

# --- wp-cli --------------------------------------------------------------
if ! command -v wp >/dev/null 2>&1; then
  curl -sO https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
  chmod +x wp-cli.phar && mv wp-cli.phar /usr/local/bin/wp
fi

# --- WordPress -----------------------------------------------------------
mkdir -p "$ROOT"
chown -R www-data:www-data "$ROOT"

sudo -u www-data -s <<EOSU
set -e
cd "$ROOT"
if [ ! -f wp-settings.php ]; then
  wp core download --locale=ru_RU
fi
if [ ! -f wp-config.php ]; then
  wp config create --dbname="${DB_NAME}" --dbuser="${DB_USER}" --dbpass="${DB_PASS}" --locale=ru_RU
fi
if ! wp core is-installed; then
  wp core install --url="http://${IP}" --title="mac:lab" \
    --admin_user="${WP_ADMIN}" --admin_password="${WP_PASS}" --admin_email="${WP_EMAIL}" --skip-email
fi
wp option update blogdescription "Магазин техники Apple"
wp rewrite structure '/%postname%/' --hard || true
EOSU

# --- nginx ---------------------------------------------------------------
cat > /etc/nginx/sites-available/maclab <<NGINX
server {
    listen 80 default_server;
    listen [::]:80 default_server;
    server_name _;
    root ${ROOT};
    index index.php index.html;

    client_max_body_size 64M;

    location / {
        try_files \$uri \$uri/ /index.php?\$args;
    }

    location ~ \.php\$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php${PHPVER}-fpm.sock;
    }

    location ~* \.(jpg|jpeg|png|webp|svg|woff2|css|js)\$ {
        expires 30d;
        access_log off;
    }

    location ~ /\.(?!well-known) { deny all; }
}
NGINX
ln -sf /etc/nginx/sites-available/maclab /etc/nginx/sites-enabled/maclab
rm -f /etc/nginx/sites-enabled/default
nginx -t && systemctl reload nginx
systemctl enable --now nginx mariadb "php${PHPVER}-fpm" >/dev/null 2>&1 || true

# --- загрузка лимитов PHP (медиафайлы) -----------------------------------
PHPINI="/etc/php/${PHPVER}/fpm/php.ini"
if [ -f "$PHPINI" ]; then
  sed -i 's/^upload_max_filesize = .*/upload_max_filesize = 64M/' "$PHPINI"
  sed -i 's/^post_max_size = .*/post_max_size = 64M/' "$PHPINI"
  systemctl reload "php${PHPVER}-fpm"
fi

# --- фаервол -------------------------------------------------------------
if command -v ufw >/dev/null 2>&1; then
  ufw allow OpenSSH >/dev/null 2>&1 || true
  ufw allow 'Nginx Full' >/dev/null 2>&1 || true
  yes | ufw enable >/dev/null 2>&1 || true
fi

cat > /root/maclab-credentials.txt <<TXT
Сайт:        http://${IP}
Админка:     http://${IP}/wp-admin
Логин:       ${WP_ADMIN}
Пароль:      ${WP_PASS}

База данных: ${DB_NAME}
Пользователь:${DB_USER}
Пароль БД:   ${DB_PASS}
TXT
chmod 600 /root/maclab-credentials.txt

echo
echo "==================== ГОТОВО ===================="
cat /root/maclab-credentials.txt
echo "==============================================="
echo "Дальше: закинуть maclab.zip в ${ROOT}/wp-content/themes и выполнить setup-theme.sh"
