#!/usr/bin/env bash
# Menyiapkan paket demo (demo.sql + uploads.zip) dari database lokal.
# Pakai: bash bin/package-demo.sh [--inquiries]
set -euo pipefail
cd "$(dirname "$0")/.."

command -v zip >/dev/null || { echo "Perintah zip belum ada. Jalankan: pkg install zip"; exit 1; }
command -v mysqldump >/dev/null || { echo "mysqldump belum ada. Jalankan: pkg install mariadb"; exit 1; }

php bin/package-demo.php "$@"

echo
ls -lh deploy/demo.sql deploy/uploads.zip 2>/dev/null || true
echo
echo "Salin ke penyimpanan HP supaya bisa diunggah dari browser:"
echo "  cp deploy/demo.sql deploy/uploads.zip ~/storage/downloads/"