# Memasang data demo di hosting

Untuk demo portofolio. Data demo berisi perusahaan fiktif (Karya Mandala), jadi jangan dipakai untuk klien sungguhan.

## 1. Siapkan paket (lokal)

```bash
cd ~/company-profile
php bin/seed.php --fresh        # isi database lokal dengan data demo
bash bin/package-demo.sh        # tambahkan --inquiries kalau pesan contoh ingin ikut
cp deploy/demo.sql deploy/uploads.zip ~/storage/downloads/
```

## 2. Pasang di hosting

Lakukan setelah website terpasang sesuai `docs/deployment.md` sampai langkah 6 (database terisi, akun admin dibuat).

1. phpMyAdmin, pilih database, tab **Import**, pilih `demo.sql`, **Import**.
   Kalau file lebih besar dari batas impor phpMyAdmin, naikkan `upload_max_filesize` dan `post_max_size`, atau pecah file.
2. File Manager, unggah `uploads.zip` ke folder `public/uploads`, lalu **Extract**. Folder `2026/11` dan sejenisnya akan muncul.
3. Login ke `/admin`. Cek **Media**, **Halaman**, dan **Layanan** sudah terisi.
4. Buka beranda dan telusuri semua menu.
5. Hapus `demo.sql` dan `uploads.zip` dari hosting.

## 3. Catatan

- Pengindeksan Google sengaja `noindex`. Biarkan begitu untuk demo.
- Gunakan password admin yang kuat. Demo publik tetap bisa diserang.
- Impor ulang ke database yang sudah berisi konten akan gagal (duplikat). Untuk mengganti demo, buat database baru atau kosongkan tabel konten lewat phpMyAdmin dulu.
- Mengganti demo menjadi data klien: hapus semua konten di admin, ganti pengaturan, lalu isi konten asli. Testimoni, klien, alamat, dan teks hukum di demo semuanya fiktif.