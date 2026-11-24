#!/usr/bin/env bash
# Menyiapkan paket upload untuk shared hosting. Jalankan dari folder proyek: bash bin/package.sh
set -euo pipefail
cd "$(dirname "$0")/.."

command -v zip >/dev/null || { echo "Perintah zip belum ada. Jalankan: pkg install zip"; exit 1; }

echo "1/5 Build aset frontend"
npm run build
[ -f public/assets/manifest.json ] || { echo "public/assets/manifest.json tidak ada. Build gagal."; exit 1; }

echo "2/5 Pasang dependency PHP tanpa paket dev"
composer install --no-dev --optimize-autoloader --no-interaction

echo "3/5 Buat database/install.sql"
php bin/build-install.php

echo "4/5 Susun paket"
rm -rf deploy
mkdir -p deploy/stage/public/uploads
mkdir -p deploy/stage/storage/logs deploy/stage/storage/sessions deploy/stage/storage/cache

cp -r app config database routes vendor deploy/stage/
cp .env.production.example deploy/stage/
cp public/index.php public/.htaccess deploy/stage/public/
cp -r public/assets deploy/stage/public/
cp public/uploads/.htaccess deploy/stage/public/uploads/

touch deploy/stage/storage/logs/.gitkeep deploy/stage/storage/sessions/.gitkeep deploy/stage/storage/cache/.gitkeep

# Lapisan tambahan: folder aplikasi menolak akses web kalau salah ditempatkan
for d in app config database routes storage vendor; do
    printf '<IfModule mod_authz_core.c>\n    Require all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\n    Deny from all\n</IfModule>\n' > "deploy/stage/$d/.htaccess"
done

echo "5/5 Buat zip"
(cd deploy/stage && zip -rq ../app.zip app config database routes vendor storage .env.production.example)
(cd deploy/stage/public && zip -rq ../../public.zip .)
cp database/install.sql deploy/install.sql
rm -rf deploy/stage

echo
ls -lh deploy
echo
echo "Selesai. Salin ke penyimpanan HP supaya bisa diunggah dari browser:"
echo "  termux-setup-storage   (sekali saja)"
echo "  cp deploy/*.zip deploy/install.sql ~/storage/downloads/"