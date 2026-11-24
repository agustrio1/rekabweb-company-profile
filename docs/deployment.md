# Panduan deployment ke shared hosting

Untuk hosting cPanel (atau panel sejenis) dengan PHP 8.2+, MySQL/MariaDB, File Manager, dan phpMyAdmin.
Tidak perlu SSH, Node.js, atau Composer di hosting.

## 1. Siapkan paket (di komputer atau HP lokal)

```bash
cd ~/company-profile
bash bin/package.sh
php bin/admin-sql.php > deploy/admin.sql
termux-setup-storage          # sekali saja
cp deploy/*.zip deploy/*.sql ~/storage/downloads/
```

Hasilnya di `deploy/`:

| File | Isi |
|---|---|
| `app.zip` | Kode aplikasi, `vendor/`, folder `storage/` kosong, `.env.production.example` |
| `public.zip` | Isi folder `public/` (index.php, .htaccess, assets, uploads/.htaccess) |
| `install.sql` | Struktur database lengkap |
| `admin.sql` | Perintah membuat akun Super Admin pertama (berisi hash password, jangan dibagikan) |

## 2. Database (cPanel, MySQL Databases)

1. Buat database, misalnya `akun_company`.
2. Buat user database dengan password kuat.
3. Tambahkan user ke database dengan **All Privileges**.
4. Catat nama database, user, dan password. Nama biasanya berawalan nama akun cPanel.

## 3. Versi PHP dan batas upload

- **MultiPHP Manager:** pilih PHP 8.2 atau lebih baru untuk domain ini.
- **MultiPHP INI Editor:** `upload_max_filesize = 8M`, `post_max_size = 32M`, `memory_limit = 256M`.
- Pastikan ekstensi aktif: `pdo_mysql`, `mbstring`, `fileinfo`, `gd`, `ctype`, `json`, `openssl`. Opsional: `exif`.

## 4. Upload dan susunan folder

Di File Manager, aktifkan **Settings, Show Hidden Files** supaya `.env` dan `.htaccess` terlihat.
Pilih salah satu layout.

### Layout A: document root bisa diarahkan ke folder `public`

1. Buat folder `/home/akun/cp-app`. Unggah `app.zip` ke sana, lalu **Extract**.
2. Unggah `public.zip` ke `/home/akun/cp-app/public`, lalu **Extract**.
3. Di **Domains**, ubah **Document Root** domain menjadi `cp-app/public`.

### Layout B: hanya `public_html` yang bisa dipakai sebagai document root

1. Buat folder `/home/akun/cp-app` (di luar `public_html`). Unggah dan extract `app.zip` di sana.
2. Unggah `public.zip` ke `public_html`, lalu **Extract**.
3. Di `public_html`, buat file bernama `.app-path` berisi satu baris:
   ```
   /home/akun/cp-app
   ```
   Path `/home/akun` terlihat di bagian atas File Manager. File ini tidak tertimpa saat update.

Jangan menaruh folder aplikasi di dalam `public_html`.

## 5. Konfigurasi `.env`

Di `cp-app`, salin `.env.production.example` menjadi `.env`, lalu edit.

- `APP_URL=https://domainkamu.com` (tanpa garis miring di akhir)
- `APP_ENV=production` dan `APP_DEBUG=false`
- `DB_HOST=localhost`, lalu isi `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- `APP_KEY`: isi hasil `php -r 'echo bin2hex(random_bytes(32));'` dari komputer lokal
- Memakai Cloudflare? Isi `TRUST_PROXY=true`, dan di Cloudflare pilih SSL mode **Full**.

Permission `.env`: 600 atau 640.

## 6. Import database dan buat admin

1. phpMyAdmin, pilih database, tab **Import**, pilih `install.sql`, **Import**. Database harus kosong.
2. Tab **SQL**, tempel isi `admin.sql`, **Go**.

## 7. Permission

- Folder 755 dan file 644 (bawaan File Manager).
- `storage/` dan `public/uploads/` harus bisa ditulis. Kalau muncul "Tidak bisa ditulis", ubah jadi 775.

## 8. Domain dan HTTPS

1. cPanel, **SSL/TLS Status**, jalankan **AutoSSL** untuk domain (dan `www`).
2. Setelah sertifikat aktif, buka `https://domainkamu.com`.
3. Alihan HTTP ke HTTPS berjalan otomatis dari aplikasi selama `APP_URL` berawalan `https://`.
4. Alihkan `www` ke domain utama lewat cPanel, **Redirects** (301).

## 9. Periksa server

Login di `https://domainkamu.com/admin/login`, lalu buka **Migrasi dan server**.

- Semua baris harus **OK**. Baris **Gagal** wajib diperbaiki.
- Baris **Perhatian** boleh diabaikan kalau memang opsional (email, exif).
- Hapus `admin.sql` dan `install.sql` dari hosting kalau sempat terunggah.

## 10. Isi awal website

1. **Pengaturan website:** nama, logo, favicon, kontak, WhatsApp, SEO, gambar OG.
2. **Halaman:** Tentang (slug `tentang`), Kebijakan Privasi (`kebijakan-privasi`), Syarat dan Ketentuan (`syarat-ketentuan`).
3. Layanan, proyek, artikel, testimoni, FAQ, lalu atur **Beranda**.
4. Di **Pengaturan, SEO**, pastikan **Mesin pencari** diizinkan.
5. Kirim satu pesan lewat `/kontak` dan pastikan masuk ke admin (dan ke email kalau diaktifkan).

## 11. Pengujian di hosting

| No | Uji | Hasil yang diharapkan |
|---|---|---|
| 1 | `http://domainkamu.com` | Dialihkan ke `https://` |
| 2 | `https://domainkamu.com/.env` | 404 |
| 3 | `https://domainkamu.com/app/bootstrap.php` | 404 |
| 4 | `https://domainkamu.com/storage/logs/` | 404 |
| 5 | `https://domainkamu.com/uploads/` | Bukan daftar file (403 atau 404) |
| 6 | Unggah `uploads/tes.php` berisi `<?php echo "EXEC";`, buka di browser | 403, tidak mencetak EXEC. Hapus file setelahnya. |
| 7 | `https://domainkamu.com/_dev/error` | 404 |
| 8 | `curl -I https://domainkamu.com` | Ada CSP, HSTS, X-Content-Type-Options, X-Frame-Options |
| 9 | `/sitemap.xml` dan `/robots.txt` | Terbuka, URL berawalan `https://` |
| 10 | `/admin` tanpa login | Redirect ke login. Login salah 5 kali: diblokir 15 menit |
| 11 | Unggah gambar besar | Menjadi WebP, maksimal 1920 px |
| 12 | `curl -I https://domainkamu.com/assets/<file>` | `Cache-Control: public, max-age=31536000, immutable` |
| 13 | Halaman 404 dan error | Tidak memuat detail teknis |
| 14 | Chrome Android | Menu, formulir, dan admin berfungsi tanpa error di Console |

## 12. Backup dan restore

**Backup (mingguan, dan sebelum setiap update):**
1. phpMyAdmin, pilih database, **Export**, metode Quick, format SQL, simpan file.
2. File Manager, kompres folder `public/uploads` menjadi zip, unduh.
3. Simpan salinan `.env` di tempat aman.

**Restore:** buat database kosong, **Import** file SQL hasil export, kembalikan folder `uploads`, lalu isi `.env`.

## 13. Update versi

1. Backup (langkah 12).
2. Di komputer lokal: `bash bin/package.sh`.
3. Hapus folder `public/assets` lama di hosting (nama file berhash).
4. Unggah dan extract `public.zip` ke folder public, `app.zip` ke `cp-app` (timpa semua).
   `.env`, `.app-path`, `uploads/`, dan log tidak tertimpa karena tidak ada di zip.
5. Admin, **Migrasi dan server**, jalankan migration yang menunggu.
6. Buka beberapa halaman dan cek bagian **Pemeriksaan server**.

## 14. Pemecahan masalah

| Gejala | Penyebab dan solusi |
|---|---|
| Halaman putih atau 500 | Baca `storage/logs/app-<tanggal>.log` |
| "Folder aplikasi tidak ditemukan" | Periksa isi `.app-path` (path absolut, tanpa spasi) |
| "Koneksi database gagal" | Periksa `DB_*`. `DB_HOST` biasanya `localhost`, dan nama berawalan akun |
| Semua halaman selain beranda 404 | `.htaccess` tidak dibaca. Cek `AllowOverride` atau minta hosting mengaktifkan mod_rewrite |
| 500 setelah unggah `.htaccess` | Hapus baris `Options` di `public/.htaccess` (sebagian hosting melarangnya) |
| Halaman tanpa gaya | `public/assets/manifest.json` tidak ada. Extract ulang `public.zip` |
| Redirect berulang (loop) | Di balik Cloudflare atau proxy: isi `TRUST_PROXY=true`, atau `FORCE_HTTPS=false` sementara |
| "HTMLPurifier belum terpasang" | Folder `vendor/` belum terunggah lengkap |
| Tidak bisa login, kembali ke form | `storage/sessions` tidak bisa ditulis, atau `APP_URL` tidak sesuai alamat yang dibuka |
| Gambar tidak mau diunggah | Naikkan `upload_max_filesize` dan `post_max_size`, periksa permission `public/uploads` |
| Email tidak terkirim | Cek log. Pastikan `MAIL_FROM_ADDRESS` sama dengan akun email di domain sendiri |

## Checklist keamanan sebelum tayang

- [ ] `APP_ENV=production`, `APP_DEBUG=false`
- [ ] `.env` di luar document root, permission 600 atau 640
- [ ] HTTPS aktif dan HSTS muncul
- [ ] Password Super Admin kuat, dan tidak ada akun uji tersisa
- [ ] Uji 2 sampai 7 di atas lolos
- [ ] `install.sql` dan `admin.sql` tidak ada di hosting
- [ ] Mesin pencari diizinkan, sitemap terbaca
- [ ] Backup pertama sudah diambil

