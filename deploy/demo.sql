-- Data demo, dibuat otomatis oleh bin/package-demo.php
-- Dibuat: 2026-11-24 07:39:04
-- Impor SETELAH install.sql dan admin.sql, ke database yang belum berisi konten.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

/*M!999999\- enable the sandbox mode */ 

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
/*!40000 ALTER TABLE `media` DISABLE KEYS */;
INSERT INTO `media` (`id`, `path`, `original_name`, `mime`, `size`, `width`, `height`, `alt_text`, `uploaded_by`, `created_at`) VALUES (1,'2026/11/7dd060ac157983dc4a519dd2532438d4.webp','contoh-hero.webp','image/webp',27032,1200,1500,'Ilustrasi contoh untuk bagian hero',1,'2026-11-24 07:13:58'),
(2,'2026/11/3b15aa482d05bd502b327c055fa2f1cb.webp','contoh-about.webp','image/webp',14520,1600,1200,'Ilustrasi contoh untuk profil perusahaan',1,'2026-11-24 07:13:59'),
(3,'2026/11/a02cf2d12e84dbd6e01bdf5ac29a1021.webp','contoh-og.webp','image/webp',12246,1200,630,'Gambar contoh untuk pratinjau tautan',1,'2026-11-24 07:13:59'),
(4,'2026/11/ae3a956d07355ff231c2e9c95ab6e36d.png','contoh-favicon.png','image/png',2035,256,256,'Ikon situs',1,'2026-11-24 07:13:59'),
(5,'2026/11/9e11ae4473e0d5b0708d33d5b7819310.webp','contoh-svc1.webp','image/webp',14632,1600,1000,'Ilustrasi contoh layanan renovasi',1,'2026-11-24 07:13:59'),
(6,'2026/11/4f05305790d9eaf813539879aae2e265.webp','contoh-svc2.webp','image/webp',22896,1600,1000,'Ilustrasi contoh layanan pembangunan',1,'2026-11-24 07:14:00'),
(7,'2026/11/ea3a646f0eba47a04b0e1098c131c1bc.webp','contoh-svc3.webp','image/webp',15424,1600,1000,'Ilustrasi contoh layanan interior',1,'2026-11-24 07:14:00'),
(8,'2026/11/c34ff8627e8507e6f574fba0b5563261.webp','contoh-svc4.webp','image/webp',14562,1600,1000,'Ilustrasi contoh layanan perawatan gedung',1,'2026-11-24 07:14:00'),
(9,'2026/11/d5a9b1da6b1a5ddc19f79110ef1466cd.webp','contoh-prj1.webp','image/webp',13556,1600,1000,'Ilustrasi contoh proyek renovasi rumah',1,'2026-11-24 07:14:01'),
(10,'2026/11/d1944a4ba0bca978e928f5bc11a53fc7.webp','contoh-prj2.webp','image/webp',13280,1600,1000,'Ilustrasi contoh proyek perluasan ruko',1,'2026-11-24 07:14:01'),
(11,'2026/11/8c4bdc831238ab645db796d0bffab051.webp','contoh-prj3.webp','image/webp',17122,1600,1000,'Ilustrasi contoh proyek interior kantor',1,'2026-11-24 07:14:02'),
(12,'2026/11/1b81e72a2306b79d0a96ac8c17f5f043.webp','contoh-prj4.webp','image/webp',25574,1600,1000,'Ilustrasi contoh proyek gudang',1,'2026-11-24 07:14:02'),
(13,'2026/11/c609c6cfb4e9bbb0758bc00523cb6e0d.webp','contoh-prj5.webp','image/webp',14198,1600,1000,'Ilustrasi contoh proyek dapur',1,'2026-11-24 07:14:02'),
(14,'2026/11/3b3528052df272258f541c3c405bea12.webp','contoh-prj6.webp','image/webp',20598,1600,1000,'Ilustrasi contoh proyek kafe',1,'2026-11-24 07:14:03'),
(15,'2026/11/0d362e1c21cd6f51f1ada04f11274cd2.webp','contoh-g1a.webp','image/webp',20896,1600,1200,'Ilustrasi galeri proyek 1, tampak depan',1,'2026-11-24 07:14:03'),
(16,'2026/11/aad010ef11a1464255c263286d3eae4b.webp','contoh-g1b.webp','image/webp',27212,1600,1200,'Ilustrasi galeri proyek 1, ruang keluarga',1,'2026-11-24 07:14:04'),
(17,'2026/11/8e70b6a4f0e244268d19e33e6f9cffab.webp','contoh-g1c.webp','image/webp',21568,1600,1200,'Ilustrasi galeri proyek 1, dapur',1,'2026-11-24 07:14:04'),
(18,'2026/11/84561aa5cd1f296ef719bd33e4fc89dc.webp','contoh-g2a.webp','image/webp',18636,1600,1200,'Ilustrasi galeri proyek 2, lantai satu',1,'2026-11-24 07:14:05'),
(19,'2026/11/939766a27d4d30004cd1b9665d263c5f.webp','contoh-g2b.webp','image/webp',18704,1600,1200,'Ilustrasi galeri proyek 2, lantai dua',1,'2026-11-24 07:14:05'),
(20,'2026/11/18f4fceeba0d8ca762e48b73ab82a37b.webp','contoh-art1.webp','image/webp',23806,1600,1000,'Ilustrasi artikel tentang RAB',1,'2026-11-24 07:14:06'),
(21,'2026/11/c1be6f97d4d7b0fa44132d464c4dcefb.webp','contoh-art2.webp','image/webp',17568,1600,1000,'Ilustrasi artikel tentang survei lokasi',1,'2026-11-24 07:14:06'),
(22,'2026/11/d68b22868f2a796a9d37456d51a1fa4b.webp','contoh-art3.webp','image/webp',13798,1600,1000,'Ilustrasi artikel tentang perizinan',1,'2026-11-24 07:14:07'),
(23,'2026/11/0dcebc59573be756e36a7ba6c52e65c4.webp','contoh-art4.webp','image/webp',21392,1600,1000,'Ilustrasi artikel tentang material lantai',1,'2026-11-24 07:14:07'),
(24,'2026/11/4ad0b8eb7f7acd9a9d23e6a4e2c1cf54.webp','contoh-art5.webp','image/webp',17776,1600,1000,'Ilustrasi artikel tentang dapur kecil',1,'2026-11-24 07:14:08'),
(25,'2026/11/1aa45d297fac60940896fdfcaff89838.webp','contoh-art6.webp','image/webp',19092,1600,1000,'Ilustrasi artikel tentang atap bocor',1,'2026-11-24 07:14:09');
/*!40000 ALTER TABLE `media` ENABLE KEYS */;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` (`setting_key`, `setting_value`, `updated_at`) VALUES ('address','Jl. Contoh Raya No. 12\nKota Bandung, Jawa Barat 40100','2026-11-24 07:14:09'),
('business_hours','Senin - Jumat: 08.00 - 17.00\nSabtu: 08.00 - 13.00','2026-11-24 07:14:09'),
('company_name','Karya Mandala','2026-11-24 07:14:09'),
('copyright','Seluruh hak cipta dilindungi.','2026-11-24 07:14:09'),
('cta_label','Konsultasi Gratis','2026-11-24 07:14:09'),
('cta_url','/kontak','2026-11-24 07:14:09'),
('description','Karya Mandala adalah kontraktor renovasi, pembangunan, dan interior di Bandung. Kami mengerjakan rumah tinggal, ruko, dan kantor dengan rencana kerja tertulis dan laporan progres berkala.','2026-11-24 07:14:09'),
('email','halo@karyamandala.example','2026-11-24 07:14:09'),
('favicon','4','2026-11-24 07:14:09'),
('footer_text','','2026-11-24 07:14:09'),
('og_image','3','2026-11-24 07:14:09'),
('phone','022 1234 5678','2026-11-24 07:14:09'),
('robots','noindex','2026-11-24 07:14:09'),
('seo_description','Kontraktor renovasi, pembangunan, dan interior di Bandung. Rencana kerja tertulis, biaya dirinci, dan laporan progres berkala. Konsultasi awal gratis.','2026-11-24 07:14:09'),
('seo_title','Karya Mandala - Kontraktor Renovasi dan Interior Bandung','2026-11-24 07:14:09'),
('tagline','Renovasi, pembangunan, dan interior untuk rumah dan usaha','2026-11-24 07:14:09'),
('whatsapp','6281234567890','2026-11-24 07:14:09');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
/*!40000 ALTER TABLE `homepage_sections` DISABLE KEYS */;
INSERT INTO `homepage_sections` (`section_key`, `is_active`, `sort_order`, `content`, `updated_at`) VALUES ('about',1,2,'{\"title\":\"Tentang Karya Mandala\",\"text\":\"Kami adalah tim kecil yang mengerjakan renovasi, pembangunan, dan interior untuk rumah tinggal, ruko, dan kantor di Bandung Raya.\\n\\nSetiap proyek dipegang satu penanggung jawab, dari survei sampai serah terima, supaya Anda tidak perlu menjelaskan hal yang sama berulang kali.\",\"link_label\":\"Selengkapnya tentang kami\",\"link_url\":\"/tentang\",\"image\":\"2\"}','2026-11-24 07:14:09'),
('advantages',1,4,'{\"title\":\"Mengapa memilih kami\",\"subtitle\":\"\",\"items\":[{\"title\":\"Rencana kerja tertulis\",\"text\":\"Lingkup, jadwal, dan rincian biaya disepakati tertulis sebelum pekerjaan dimulai.\"},{\"title\":\"Satu penanggung jawab\",\"text\":\"Anda berhubungan dengan orang yang sama dari survei sampai serah terima.\"},{\"title\":\"Laporan progres berkala\",\"text\":\"Foto dan catatan perkembangan dikirim rutin, jadi Anda tidak perlu menebak-nebak.\"},{\"title\":\"Biaya yang dirinci\",\"text\":\"Material, upah, dan pekerjaan tambahan dijelaskan per item, tanpa angka gelondongan.\"}]}','2026-11-24 07:14:09'),
('articles',1,11,'{\"title\":\"Artikel terbaru\",\"subtitle\":\"\",\"count\":\"3\"}','2026-11-24 07:14:09'),
('cta',1,10,'{\"title\":\"Siap berdiskusi tentang kebutuhan Anda?\",\"text\":\"Sampaikan kebutuhan Anda dan kami balas dengan langkah berikutnya.\",\"button_label\":\"Konsultasi Gratis\",\"button_url\":\"/kontak\"}','2026-11-24 07:14:09'),
('faq',1,9,'{\"title\":\"Pertanyaan umum\",\"subtitle\":\"\",\"count\":\"4\"}','2026-11-24 07:14:09'),
('hero',1,1,'{\"eyebrow\":\"Kontraktor renovasi dan interior di Bandung\",\"headline\":\"Renovasi dan pembangunan yang selesai sesuai rencana\",\"description\":\"Ceritakan kebutuhan Anda. Kami menjawab dengan rencana kerja tertulis, rincian biaya, dan jadwal yang bisa dipegang, lalu melaporkan progres sampai serah terima.\",\"cta_label\":\"Konsultasi Gratis\",\"cta_url\":\"/kontak\",\"secondary_label\":\"Lihat layanan\",\"secondary_url\":\"/layanan\",\"image\":\"1\"}','2026-11-24 07:14:09'),
('portfolio',1,6,'{\"title\":\"Proyek terpilih\",\"subtitle\":\"\",\"count\":\"3\"}','2026-11-24 07:14:09'),
('process',1,7,'{\"title\":\"Cara kami bekerja\",\"subtitle\":\"\",\"items\":[{\"title\":\"Konsultasi\",\"text\":\"Kami mendengar kebutuhan dan kondisi lapangan Anda.\"},{\"title\":\"Penawaran\",\"text\":\"Anda menerima lingkup kerja, jadwal, dan rincian biaya tertulis.\"},{\"title\":\"Pengerjaan\",\"text\":\"Pekerjaan dijalankan sesuai rencana dengan laporan progres.\"},{\"title\":\"Serah terima\",\"text\":\"Hasil diperiksa bersama dan diserahkan lengkap dengan dokumennya.\"}]}','2026-11-24 07:14:09'),
('services',1,3,'{\"title\":\"Layanan kami\",\"subtitle\":\"Layanan yang paling sering diminta klien.\",\"count\":\"3\"}','2026-11-24 07:14:09'),
('stats',0,5,'{\"title\":\"\",\"items\":[]}','2026-11-24 07:14:09'),
('testimonials',1,8,'{\"title\":\"Kata klien\",\"subtitle\":\"\",\"count\":\"3\"}','2026-11-24 07:14:09');
/*!40000 ALTER TABLE `homepage_sections` ENABLE KEYS */;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
/*!40000 ALTER TABLE `pages` DISABLE KEYS */;
INSERT INTO `pages` (`id`, `title`, `slug`, `content`, `featured_image_id`, `status`, `published_at`, `seo_title`, `seo_description`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES (1,'Tentang Kami','tentang','<h2>Siapa kami</h2><p>Karya Mandala adalah kontraktor renovasi, pembangunan, dan interior yang berbasis di Bandung. Kami mengerjakan rumah tinggal, ruko, dan kantor, dari perbaikan ringan sampai perubahan tata ruang.</p><p>Kami memilih tetap berukuran kecil supaya setiap proyek mendapat perhatian langsung dari orang yang bertanggung jawab atasnya.</p><h2>Visi dan misi</h2><p>Visi kami sederhana: menjadi kontraktor yang dipercaya karena pekerjaannya terbuka dan bisa diperiksa.</p><ul><li>Menjelaskan lingkup, jadwal, dan biaya secara tertulis sebelum pekerjaan dimulai.</li><li>Melaporkan progres secara berkala dengan foto dan catatan.</li><li>Menyerahkan hasil pekerjaan setelah diperiksa bersama pemilik.</li></ul><h2>Nilai kami</h2><ul><li><strong>Terbuka.</strong> Kami menjelaskan apa yang dikerjakan dan mengapa biayanya demikian.</li><li><strong>Tepat waktu.</strong> Jadwal disusun dari kemampuan nyata tim, bukan dari janji.</li><li><strong>Rapi.</strong> Area kerja dijaga bersih supaya aktivitas Anda tetap berjalan.</li><li><strong>Bertanggung jawab.</strong> Kekurangan pekerjaan yang ditemukan saat serah terima kami perbaiki.</li></ul><h2>Cara kami bekerja</h2><p>Kami mulai dari survei lokasi dan mendengarkan kebutuhan Anda. Dari situ kami menyusun gambar kerja dan rencana anggaran biaya, lalu mengerjakannya bertahap dengan laporan progres. Di akhir, kami memeriksa hasilnya bersama Anda sebelum serah terima.</p>',NULL,'published','2026-09-25 06:13:58','','Mengenal Karya Mandala, kontraktor renovasi, pembangunan, dan interior di Bandung.',1,1,'2026-11-24 07:14:09','2026-11-24 07:14:09'),
(2,'Kebijakan Privasi','kebijakan-privasi','<p>Kebijakan ini menjelaskan data yang kami kumpulkan lewat website ini dan cara kami menggunakannya. Teks ini adalah templat umum dan perlu ditinjau sesuai kebutuhan usaha Anda.</p><h2>Data yang kami kumpulkan</h2><p>Data hanya kami terima ketika Anda mengirim formulir kontak: nama, email, nomor WhatsApp, nama perusahaan (jika diisi), jenis kebutuhan, dan isi pesan. Untuk mencegah spam, alamat IP pengirim juga tercatat bersama pesan.</p><h2>Cara kami menggunakan data</h2><ul><li>Membalas pesan dan menindaklanjuti kebutuhan Anda.</li><li>Mencatat riwayat komunikasi agar tidak ada permintaan yang terlewat.</li><li>Mencegah penyalahgunaan formulir.</li></ul><p>Kami tidak menjual atau membagikan data Anda kepada pihak lain untuk keperluan pemasaran.</p><h2>Cookie dan pelacakan</h2><p>Halaman publik website ini tidak memasang cookie pelacak dan tidak memakai layanan analitik pihak ketiga.</p><h2>Penyimpanan dan penghapusan</h2><p>Pesan Anda disimpan selama masih diperlukan untuk menindaklanjuti kebutuhan Anda. Anda dapat meminta data Anda dihapus dengan menghubungi kami lewat kontak di halaman Kontak.</p><h2>Perubahan kebijakan</h2><p>Kebijakan ini dapat diperbarui sewaktu-waktu. Versi terbaru selalu tersedia di halaman ini.</p>',NULL,'published','2026-09-26 06:13:58','','Data apa yang dikumpulkan website Karya Mandala dan bagaimana data itu digunakan.',1,1,'2026-11-24 07:14:09','2026-11-24 07:14:09'),
(3,'Syarat dan Ketentuan','syarat-ketentuan','<p>Dengan menggunakan website ini, Anda menyetujui ketentuan berikut. Teks ini adalah templat umum dan perlu ditinjau sesuai kebutuhan usaha Anda.</p><h2>Penggunaan website</h2><p>Informasi di website ini bersifat umum dan dapat berubah tanpa pemberitahuan. Isi website bukan penawaran yang mengikat. Penawaran resmi hanya berlaku bila diberikan tertulis oleh kami.</p><h2>Layanan dan penawaran</h2><ul><li>Lingkup pekerjaan, jadwal, dan biaya ditetapkan dalam penawaran atau kontrak tertulis.</li><li>Perubahan lingkup setelah pekerjaan dimulai dibicarakan dan disepakati terlebih dahulu.</li></ul><h2>Hak cipta</h2><p>Teks, gambar, dan desain di website ini dimiliki Karya Mandala kecuali dinyatakan lain. Penggunaan ulang memerlukan izin tertulis.</p><h2>Batasan tanggung jawab</h2><p>Kami berupaya menjaga keakuratan informasi di website ini, tetapi tidak menjamin bahwa seluruh isinya selalu lengkap dan terbaru.</p><h2>Kontak</h2><p>Pertanyaan tentang ketentuan ini dapat disampaikan lewat halaman Kontak.</p>',NULL,'published','2026-09-27 06:13:58','','Ketentuan penggunaan website Karya Mandala.',1,1,'2026-11-24 07:14:09','2026-11-24 07:14:09');
/*!40000 ALTER TABLE `pages` ENABLE KEYS */;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
/*!40000 ALTER TABLE `services` DISABLE KEYS */;
INSERT INTO `services` (`id`, `title`, `slug`, `summary`, `content`, `featured_image_id`, `details`, `cta_label`, `cta_url`, `is_featured`, `status`, `published_at`, `sort_order`, `seo_title`, `seo_description`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES (1,'Renovasi Rumah dan Ruko','renovasi-rumah-dan-ruko','Perbaikan dan penataan ulang rumah, ruko, atau kantor, dari pekerjaan ringan sampai perubahan tata ruang.','<h2>Apa yang kami kerjakan</h2><p>Kami menangani renovasi menyeluruh maupun per bagian. Setiap pekerjaan dimulai dari survei lokasi supaya rencana dan anggaran didasarkan pada kondisi bangunan yang sebenarnya.</p><ul><li>Perubahan tata ruang dan penambahan ruang.</li><li>Pekerjaan dinding, lantai, plafon, dan atap.</li><li>Perbaikan instalasi listrik dan air.</li><li>Pengecatan dan finishing.</li></ul><h2>Cocok untuk</h2><ul><li>Pemilik rumah yang ingin menata ulang ruang.</li><li>Pemilik ruko yang perlu menyesuaikan tampilan dan fungsi usaha.</li><li>Kantor kecil yang butuh ruang kerja lebih tertata.</li></ul>',5,'{\"benefits\":[{\"title\":\"Biaya dirinci per pekerjaan\",\"text\":\"Anda tahu ke mana setiap rupiah dipakai dan bisa memilih mana yang didahulukan.\"},{\"title\":\"Jadwal tertulis\",\"text\":\"Tahapan dan target selesai disepakati di awal dan dilaporkan setiap pekan.\"},{\"title\":\"Area kerja tetap rapi\",\"text\":\"Debu dan material dikelola supaya aktivitas rumah atau usaha tetap berjalan.\"}],\"process\":[{\"title\":\"Survei lokasi\",\"text\":\"Kami memeriksa kondisi bangunan dan mencatat kebutuhan Anda.\"},{\"title\":\"Gambar kerja dan RAB\",\"text\":\"Anda menerima gambar dan rincian biaya untuk disetujui.\"},{\"title\":\"Pengerjaan bertahap\",\"text\":\"Pekerjaan dijalankan sesuai jadwal dengan laporan progres.\"},{\"title\":\"Serah terima\",\"text\":\"Hasil diperiksa bersama dan kekurangan diperbaiki sebelum selesai.\"}],\"faqs\":[{\"question\":\"Apakah survei lokasi berbayar?\",\"answer\":\"Survei awal di wilayah Bandung Raya tidak dipungut biaya. Untuk lokasi di luar wilayah itu, kami informasikan dulu kalau ada biaya transportasi.\"},{\"question\":\"Berapa lama renovasi berlangsung?\",\"answer\":\"Tergantung lingkup pekerjaan. Setelah survei, kami berikan jadwal tertulis per tahapan, dan itu yang kami laporkan progresnya.\"}]}','','',1,'published','2026-10-05 06:13:58',1,'','',1,1,'2026-11-24 07:14:09','2026-11-24 07:14:09'),
(2,'Pembangunan Baru','pembangunan-baru','Pembangunan rumah, ruko, gudang kecil, dan bangunan usaha dari gambar kerja sampai serah terima.','<h2>Dari gambar sampai bangunan jadi</h2><p>Kami membangun dengan gambar kerja dan anggaran yang jelas. Pemilik dilibatkan pada keputusan penting seperti pemilihan material dan perubahan desain.</p><ul><li>Rumah tinggal satu sampai dua lantai.</li><li>Ruko dan bangunan usaha kecil.</li><li>Gudang dan ruang penyimpanan sederhana.</li></ul><h2>Yang perlu Anda siapkan</h2><p>Kepemilikan lahan yang jelas dan gambaran kebutuhan ruang. Soal gambar kerja dan perhitungan biaya, kami bantu dari sana.</p>',6,'{\"benefits\":[{\"title\":\"Anggaran terukur\",\"text\":\"RAB disusun per tahapan sehingga arus biaya mudah dipantau.\"},{\"title\":\"Pengawasan di lapangan\",\"text\":\"Ada penanggung jawab yang rutin memeriksa mutu pekerjaan.\"},{\"title\":\"Dokumen serah terima\",\"text\":\"Gambar akhir dan catatan pekerjaan diserahkan bersama bangunan.\"}],\"process\":[{\"title\":\"Konsultasi kebutuhan\",\"text\":\"Kami memahami kebutuhan ruang, anggaran, dan jadwal.\"},{\"title\":\"Perencanaan\",\"text\":\"Gambar kerja dan RAB disusun lalu disetujui bersama.\"},{\"title\":\"Konstruksi\",\"text\":\"Pekerjaan dijalankan per tahap dengan laporan progres.\"},{\"title\":\"Pemeriksaan akhir\",\"text\":\"Bangunan diperiksa bersama sebelum diserahkan.\"}],\"faqs\":[{\"question\":\"Apakah Karya Mandala membantu perizinan bangunan?\",\"answer\":\"Kami bisa membantu menyiapkan dokumen teknis. Proses pengajuannya mengikuti ketentuan pemerintah daerah setempat, dan kami jelaskan langkahnya saat konsultasi.\"},{\"question\":\"Bisakah pembayaran dilakukan bertahap?\",\"answer\":\"Bisa. Pembayaran dikaitkan dengan tahapan pekerjaan yang disepakati di kontrak.\"}]}','','',1,'published','2026-10-06 06:13:58',2,'','',1,1,'2026-11-24 07:14:09','2026-11-24 07:14:09'),
(3,'Desain dan Interior','desain-dan-interior','Perencanaan dan pengerjaan interior rumah, kafe, dan kantor yang fungsional dan sesuai anggaran.','<h2>Interior yang fungsional</h2><p>Kami merancang ruang dari cara Anda memakainya. Desain ditampilkan dalam gambar sebelum pengerjaan, dan material dipilih sesuai anggaran serta kebutuhan perawatan.</p><ul><li>Furnitur custom: lemari, meja kerja, dan kabinet dapur.</li><li>Penataan pencahayaan dan sirkulasi ruang.</li><li>Pemilihan material dan palet warna.</li></ul>',7,'{\"benefits\":[{\"title\":\"Gambar sebelum dikerjakan\",\"text\":\"Anda bisa menilai rancangan sebelum biaya produksi dikeluarkan.\"},{\"title\":\"Material sesuai anggaran\",\"text\":\"Kami memberi pilihan dengan perbandingan harga dan perawatan.\"},{\"title\":\"Pengerjaan terkoordinasi\",\"text\":\"Desain dan produksi dipegang satu tim, sehingga hasil sesuai rancangan.\"}],\"process\":[{\"title\":\"Brief dan ukur ruang\",\"text\":\"Kami mencatat kebutuhan dan mengukur ruang yang ada.\"},{\"title\":\"Konsep dan gambar\",\"text\":\"Anda menerima rancangan untuk ditinjau dan direvisi.\"},{\"title\":\"Produksi dan pemasangan\",\"text\":\"Furnitur dibuat dan dipasang sesuai gambar yang disetujui.\"},{\"title\":\"Finishing dan serah terima\",\"text\":\"Detail akhir dirapikan dan diperiksa bersama.\"}],\"faqs\":[{\"question\":\"Apakah desain bisa dipesan terpisah dari pengerjaan?\",\"answer\":\"Bisa. Anda boleh memesan desain saja, atau melanjutkan ke pengerjaan oleh tim kami.\"}]}','','',1,'published','2026-10-07 06:13:58',3,'','',1,1,'2026-11-24 07:14:09','2026-11-24 07:14:09'),
(4,'Perawatan dan Perbaikan Gedung','perawatan-dan-perbaikan-gedung','Perawatan berkala dan perbaikan cepat untuk atap, saluran, dinding, dan instalasi bangunan.','<h2>Mencegah kerusakan kecil jadi besar</h2><p>Banyak kerusakan mahal bermula dari masalah kecil yang dibiarkan. Kami memeriksa kondisi bangunan dan memperbaiki yang perlu sebelum meluas.</p><ul><li>Perbaikan atap bocor dan talang.</li><li>Perbaikan dinding lembap dan retak rambut.</li><li>Pengecekan instalasi listrik dan air.</li><li>Pengecatan ulang area terdampak.</li></ul>',8,'{\"benefits\":[{\"title\":\"Pemeriksaan dengan catatan\",\"text\":\"Temuan dicatat dan diurutkan dari yang paling mendesak.\"},{\"title\":\"Perbaikan tepat sasaran\",\"text\":\"Kami mengerjakan penyebabnya, bukan hanya menutup gejalanya.\"}],\"process\":[{\"title\":\"Pemeriksaan\",\"text\":\"Kami memeriksa area yang bermasalah.\"},{\"title\":\"Usulan perbaikan\",\"text\":\"Anda menerima rincian pekerjaan dan biaya.\"},{\"title\":\"Perbaikan\",\"text\":\"Pekerjaan dilakukan dengan area kerja yang dijaga rapi.\"}],\"faqs\":[]}','','',0,'published','2026-10-08 06:13:58',4,'','',1,1,'2026-11-24 07:14:09','2026-11-24 07:14:09');
/*!40000 ALTER TABLE `services` ENABLE KEYS */;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
/*!40000 ALTER TABLE `project_categories` DISABLE KEYS */;
INSERT INTO `project_categories` (`id`, `name`, `slug`, `created_at`, `updated_at`) VALUES (1,'Renovasi','renovasi','2026-11-24 07:14:10','2026-11-24 07:14:10'),
(2,'Pembangunan','pembangunan','2026-11-24 07:14:10','2026-11-24 07:14:10'),
(3,'Interior','interior','2026-11-24 07:14:10','2026-11-24 07:14:10');
/*!40000 ALTER TABLE `project_categories` ENABLE KEYS */;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
/*!40000 ALTER TABLE `projects` DISABLE KEYS */;
INSERT INTO `projects` (`id`, `category_id`, `title`, `slug`, `summary`, `client_name`, `show_client`, `location`, `completed_year`, `content`, `challenge`, `solution`, `results`, `featured_image_id`, `is_featured`, `status`, `published_at`, `sort_order`, `seo_title`, `seo_description`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES (1,1,'Renovasi Rumah Tinggal Dago','renovasi-rumah-tinggal-dago','Penataan ulang rumah dua lantai agar ruang keluarga, dapur, dan teras menyatu dan lebih terang.','Keluarga Hartono',0,'Bandung, Jawa Barat',2025,'<p>Rumah berusia puluhan tahun ini direnovasi untuk menyesuaikan kebutuhan keluarga yang bertambah. Pekerjaan mencakup perubahan tata ruang lantai satu, penggantian lantai, dan penataan ulang dapur.</p>','Ruang keluarga, dapur, dan ruang makan terpisah dinding sehingga rumah terasa gelap dan sempit. Pemilik tetap tinggal di rumah selama pekerjaan berlangsung.','Dinding non-struktural dibongkar setelah pemeriksaan, lalu ruang dibuka menjadi satu area. Pekerjaan dibagi per zona supaya sebagian rumah tetap bisa dipakai, dan debu dibatasi dengan sekat sementara.','Ruang keluarga dan dapur menyatu dengan cahaya alami yang lebih baik. Pekerjaan diserahkan setelah diperiksa bersama pemilik dan beberapa catatan finishing diperbaiki.',9,1,'published','2026-11-14 06:13:58',1,'','',1,1,'2026-11-24 07:14:10','2026-11-24 07:14:10'),
(2,1,'Perluasan Ruko Dua Lantai','perluasan-ruko-dua-lantai','Penambahan ruang usaha di lantai dua dan penataan ulang etalase ruko di kawasan perdagangan.','Toko Sinar Pagi',1,'Cimahi, Jawa Barat',2024,'<p>Pemilik usaha membutuhkan ruang penyimpanan dan area layanan tambahan tanpa menghentikan operasional toko.</p>','Toko harus tetap buka selama renovasi, dan akses material hanya lewat satu pintu depan.','Pekerjaan berisik dijadwalkan di luar jam ramai. Material diangkut pada jam tertentu dan area etalase ditutup sementara per bagian.','Toko memiliki lantai dua yang berfungsi sebagai gudang dan ruang layanan, dan etalase yang lebih mudah dilihat dari jalan.',10,1,'published','2026-11-04 06:13:58',2,'','',1,1,'2026-11-24 07:14:10','2026-11-24 07:14:10'),
(3,3,'Interior Kantor Notaris','interior-kantor-notaris','Penataan ruang kerja dan ruang tamu kantor profesional yang tenang, rapi, dan mudah dirawat.','',0,'Bandung, Jawa Barat',2025,'<p>Kantor ini membutuhkan ruang yang memberi kesan teratur bagi klien, sekaligus penyimpanan berkas yang memadai.</p>','Ruang sempit dengan banyak berkas fisik yang harus tetap mudah diakses dan aman.','Kami merancang lemari arsip custom setinggi plafon, meja kerja dengan laci terintegrasi, dan pencahayaan yang merata di ruang tamu.','Berkas tertata di satu dinding, dan ruang tamu terasa lebih lega untuk menerima klien.',11,0,'published','2026-10-25 06:13:58',3,'','',1,1,'2026-11-24 07:14:10','2026-11-24 07:14:10'),
(4,2,'Pembangunan Gudang Distribusi Kecil','pembangunan-gudang-distribusi-kecil','Gudang sederhana dengan rangka baja ringan dan area bongkar muat untuk usaha distribusi lokal.','CV Distribusi Contoh',0,'Bandung Barat, Jawa Barat',2024,'<p>Pembangunan gudang dari lahan kosong, mulai dari pondasi sampai atap dan area bongkar muat.</p>','Lahan sedikit berkontur dan jadwal harus selesai sebelum musim hujan.','Pematangan lahan dan pondasi dikerjakan lebih dulu, lalu rangka atap dipasang begitu pondasi siap supaya pekerjaan lanjutan terlindung dari hujan.','Gudang selesai dengan area bongkar muat yang dapat dilalui truk kecil.',12,0,'published','2026-10-10 06:13:58',4,'','',1,1,'2026-11-24 07:14:10','2026-11-24 07:14:10'),
(5,3,'Renovasi Dapur dan Ruang Makan','renovasi-dapur-dan-ruang-makan','Dapur sempit ditata ulang dengan kabinet custom dan pencahayaan kerja yang lebih baik.','',0,'Lembang, Jawa Barat',2025,'<p>Dapur dengan tata letak yang tidak efisien diubah menjadi area kerja yang lebih nyaman.</p>','Ruang dapur kecil dengan peralatan yang berserakan dan sirkulasi yang sempit.','Kabinet dibuat sesuai ukuran ruang dengan penyimpanan vertikal, dan lampu kerja dipasang di bawah kabinet atas.','Peralatan memiliki tempat tetap dan area kerja lebih longgar.',13,0,'published','2026-09-25 06:13:58',5,'','',1,1,'2026-11-24 07:14:10','2026-11-24 07:14:10'),
(6,3,'Interior Kafe Tiga Lantai','interior-kafe-tiga-lantai','Penataan interior kafe dengan area bar, ruang duduk, dan lantai atas untuk pertemuan kecil.','Kafe Contoh',0,'Bandung, Jawa Barat',2023,'<p>Kafe di bangunan tiga lantai ini membutuhkan interior yang hangat dan tahan pemakaian harian.</p>','Setiap lantai memiliki fungsi berbeda, dan material harus tahan lalu lintas pengunjung.','Kami memakai material yang mudah dibersihkan di area bar, dan pencahayaan hangat di ruang duduk untuk membedakan suasana tiap lantai.','Tiap lantai memiliki suasana sendiri dengan perawatan harian yang mudah.',14,0,'published','2026-08-26 06:13:58',6,'','',1,1,'2026-11-24 07:14:10','2026-11-24 07:14:10');
/*!40000 ALTER TABLE `projects` ENABLE KEYS */;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
/*!40000 ALTER TABLE `project_images` DISABLE KEYS */;
INSERT INTO `project_images` (`project_id`, `media_id`, `sort_order`) VALUES (1,15,1),
(1,16,2),
(1,17,3),
(2,18,1),
(2,19,2);
/*!40000 ALTER TABLE `project_images` ENABLE KEYS */;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
/*!40000 ALTER TABLE `article_categories` DISABLE KEYS */;
INSERT INTO `article_categories` (`id`, `name`, `slug`, `created_at`, `updated_at`) VALUES (1,'Tips','tips','2026-11-24 07:14:10','2026-11-24 07:14:10'),
(2,'Panduan','panduan','2026-11-24 07:14:10','2026-11-24 07:14:10');
/*!40000 ALTER TABLE `article_categories` ENABLE KEYS */;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
/*!40000 ALTER TABLE `tags` DISABLE KEYS */;
INSERT INTO `tags` (`id`, `name`, `slug`, `created_at`, `updated_at`) VALUES (1,'rab','rab','2026-11-24 07:14:10','2026-11-24 07:14:10'),
(2,'renovasi','renovasi','2026-11-24 07:14:10','2026-11-24 07:14:10'),
(3,'biaya','biaya','2026-11-24 07:14:10','2026-11-24 07:14:10'),
(4,'survei','survei','2026-11-24 07:14:10','2026-11-24 07:14:10'),
(5,'persiapan','persiapan','2026-11-24 07:14:10','2026-11-24 07:14:10'),
(6,'perizinan','perizinan','2026-11-24 07:14:10','2026-11-24 07:14:10'),
(7,'material','material','2026-11-24 07:14:10','2026-11-24 07:14:10'),
(8,'ruko','ruko','2026-11-24 07:14:10','2026-11-24 07:14:10'),
(9,'lantai','lantai','2026-11-24 07:14:10','2026-11-24 07:14:10'),
(10,'interior','interior','2026-11-24 07:14:10','2026-11-24 07:14:10'),
(11,'dapur','dapur','2026-11-24 07:14:10','2026-11-24 07:14:10'),
(12,'perawatan','perawatan','2026-11-24 07:14:10','2026-11-24 07:14:10'),
(13,'atap','atap','2026-11-24 07:14:10','2026-11-24 07:14:10');
/*!40000 ALTER TABLE `tags` ENABLE KEYS */;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
/*!40000 ALTER TABLE `articles` DISABLE KEYS */;
INSERT INTO `articles` (`id`, `category_id`, `author_id`, `title`, `slug`, `excerpt`, `content`, `featured_image_id`, `status`, `published_at`, `seo_title`, `seo_description`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES (1,2,1,'Cara membaca RAB renovasi agar tidak ada biaya tersembunyi','cara-membaca-rab-renovasi','Rencana anggaran biaya yang baik merinci pekerjaan, volume, dan harga satuan. Ini yang perlu Anda periksa sebelum menyetujuinya.','<p>Rencana anggaran biaya (RAB) adalah dokumen terpenting sebelum renovasi dimulai. Dokumen ini menentukan apa yang dikerjakan dan berapa biayanya.</p><h2>Periksa rincian pekerjaan</h2><p>RAB yang baik memuat daftar pekerjaan, volume, satuan, harga satuan, dan jumlah. Hati-hati dengan baris bertuliskan angka gelondongan tanpa rincian.</p><h2>Tanyakan hal yang tidak jelas</h2><ul><li>Apakah harga sudah termasuk material dan upah?</li><li>Pekerjaan apa yang belum termasuk?</li><li>Bagaimana pekerjaan tambahan dihitung?</li></ul><p>Kontraktor yang terbuka akan senang menjelaskan. Jawaban yang menghindar adalah tanda untuk berhati-hati.</p>',20,'published','2026-11-21 06:13:58','','Panduan membaca RAB renovasi: item yang harus dirinci, cara memeriksa volume, dan pertanyaan yang perlu diajukan ke kontraktor.',1,1,'2026-11-24 07:14:10','2026-11-24 07:14:10'),
(2,1,1,'Lima hal yang perlu disiapkan sebelum survei lokasi','persiapan-sebelum-survei-lokasi','Survei lokasi lebih efektif kalau Anda datang dengan gambaran kebutuhan. Siapkan lima hal ini.','<p>Survei lokasi adalah langkah awal yang menentukan akurasi rencana dan anggaran. Persiapan sederhana membuat survei jauh lebih berguna.</p><ul><li>Daftar kebutuhan, urut dari yang paling penting.</li><li>Foto kondisi saat ini dan masalah yang dirasakan.</li><li>Perkiraan anggaran, walau berupa rentang.</li><li>Target waktu, misalnya sebelum acara tertentu.</li><li>Dokumen bangunan seperti denah atau sertifikat, kalau ada.</li></ul><p>Jangan khawatir kalau belum lengkap. Tim survei akan membantu melengkapinya di lokasi.</p>',21,'published','2026-11-16 06:13:58','','',1,1,'2026-11-24 07:14:10','2026-11-24 07:14:10'),
(3,2,1,'Kapan renovasi butuh persetujuan bangunan?','renovasi-dan-persetujuan-bangunan','','<p>Tidak semua renovasi butuh perizinan, tetapi perubahan tertentu bisa membutuhkannya. Ketentuannya ditetapkan pemerintah daerah, jadi bisa berbeda antar wilayah.</p><h2>Perubahan yang perlu dicek</h2><ul><li>Penambahan lantai atau luas bangunan.</li><li>Perubahan fungsi bangunan, misalnya rumah menjadi tempat usaha.</li><li>Perubahan struktur utama.</li></ul><h2>Langkah yang aman</h2><p>Tanyakan ke dinas yang menangani perizinan bangunan di daerah Anda sebelum pekerjaan dimulai. Artikel ini bersifat umum dan bukan nasihat hukum.</p>',22,'published','2026-11-10 06:13:58','','Penjelasan umum kapan renovasi memerlukan persetujuan bangunan dan mengapa Anda perlu bertanya ke dinas setempat.',1,1,'2026-11-24 07:14:10','2026-11-24 07:14:10'),
(4,1,1,'Memilih material lantai untuk ruko dengan lalu lintas tinggi','material-lantai-untuk-ruko','Lantai ruko dipakai sepanjang hari. Pertimbangkan daya tahan, kemudahan bersih, dan keamanan saat basah.','<p>Lantai di area usaha menerima beban yang jauh lebih berat daripada lantai rumah. Pilihan material memengaruhi biaya perawatan jangka panjang.</p><h2>Hal yang dipertimbangkan</h2><ul><li>Ketahanan terhadap gesekan dan goresan.</li><li>Kemudahan dibersihkan.</li><li>Tingkat licin ketika basah.</li><li>Biaya pemasangan dan perbaikan.</li></ul><p>Minta contoh material dan lihat langsung sebelum memutuskan, karena warna dan tekstur di katalog bisa berbeda.</p>',23,'published','2026-11-03 06:13:58','','',1,1,'2026-11-24 07:14:10','2026-11-24 07:14:10'),
(5,1,1,'Menata dapur kecil agar tetap lega','menata-dapur-kecil','Dapur kecil bisa tetap nyaman dengan penyimpanan vertikal dan tata letak yang mengikuti alur memasak.','<p>Ruang kecil bukan berarti tidak nyaman. Kuncinya ada pada tata letak dan penyimpanan.</p><ul><li>Susun peralatan mengikuti alur: simpan, cuci, siapkan, masak.</li><li>Manfaatkan dinding untuk penyimpanan vertikal.</li><li>Pilih kabinet dengan pintu geser atau pintu yang tidak memakan ruang.</li><li>Pasang lampu kerja di bawah kabinet atas.</li></ul><p>Ukur ruang dengan teliti sebelum memesan kabinet, karena selisih beberapa sentimeter bisa menentukan.</p>',24,'published','2026-10-27 06:13:58','','',1,1,'2026-11-24 07:14:10','2026-11-24 07:14:10'),
(6,1,1,'Tanda atap bocor yang sebaiknya tidak ditunda','tanda-atap-bocor','Noda di plafon dan cat mengelupas sering jadi tanda awal kebocoran. Tangani sebelum kerusakan meluas.','<p>Kebocoran atap jarang langsung besar. Biasanya ada tanda awal yang bisa dikenali.</p><ul><li>Noda kecokelatan di plafon.</li><li>Cat dinding menggelembung atau mengelupas.</li><li>Bau lembap di ruangan atas.</li><li>Talang tersumbat atau berkarat.</li></ul><p>Perbaikan di tahap awal biasanya jauh lebih murah daripada mengganti plafon dan rangka yang sudah rusak.</p>',25,'published','2026-10-15 06:13:58','','',1,1,'2026-11-24 07:14:10','2026-11-24 07:14:10');
/*!40000 ALTER TABLE `articles` ENABLE KEYS */;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
/*!40000 ALTER TABLE `article_tag` DISABLE KEYS */;
INSERT INTO `article_tag` (`article_id`, `tag_id`) VALUES (1,1),
(1,2),
(2,2),
(3,2),
(1,3),
(2,4),
(2,5),
(3,6),
(4,7),
(4,8),
(4,9),
(5,10),
(5,11),
(6,12),
(6,13);
/*!40000 ALTER TABLE `article_tag` ENABLE KEYS */;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
/*!40000 ALTER TABLE `testimonials` DISABLE KEYS */;
INSERT INTO `testimonials` (`id`, `name`, `job_title`, `company`, `quote`, `photo_id`, `status`, `sort_order`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES (1,'Rina Kusuma','Pemilik rumah','','Dari awal semua dijelaskan tertulis, termasuk biaya dan jadwal. Saat ada tambahan pekerjaan, kami diajak bicara dulu sebelum dikerjakan.',NULL,'published',1,1,1,'2026-11-24 07:14:10','2026-11-24 07:14:10'),
(2,'Agus Prasetyo','Pemilik usaha','Toko Sinar Pagi','Toko kami tetap bisa buka selama renovasi. Tim mengatur jam kerja yang berisik di luar jam ramai, dan laporan progresnya rutin.',NULL,'published',2,1,1,'2026-11-24 07:14:10','2026-11-24 07:14:10'),
(3,'Dewi Lestari','Notaris','','Lemari arsip custom-nya pas dengan ruangan. Komunikasinya enak, dan hasil akhir sesuai gambar yang kami setujui di awal.',NULL,'published',3,1,1,'2026-11-24 07:14:10','2026-11-24 07:14:10'),
(4,'Budi Santoso','Direktur','CV Distribusi Contoh','Pembangunan gudang berjalan sesuai tahapan yang disepakati. Kami selalu tahu posisi pekerjaan dari laporan mingguan.',NULL,'published',4,1,1,'2026-11-24 07:14:10','2026-11-24 07:14:10');
/*!40000 ALTER TABLE `testimonials` ENABLE KEYS */;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
/*!40000 ALTER TABLE `faqs` DISABLE KEYS */;
INSERT INTO `faqs` (`id`, `question`, `answer`, `category`, `status`, `sort_order`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES (1,'Berapa biaya renovasi?','Biaya tergantung lingkup, material, dan kondisi bangunan. Setelah survei kami berikan RAB tertulis yang dirinci per pekerjaan, jadi Anda bisa menyesuaikan sebelum menyetujui.','Biaya','published',1,1,1,'2026-11-24 07:14:10','2026-11-24 07:14:10'),
(2,'Apakah ada biaya untuk survei lokasi?','Survei awal di wilayah Bandung Raya tidak dipungut biaya. Jika lokasi di luar wilayah itu, kami informasikan lebih dulu bila ada biaya transportasi.','Biaya','published',2,1,1,'2026-11-24 07:14:10','2026-11-24 07:14:10'),
(3,'Bagaimana sistem pembayarannya?','Pembayaran dibagi per tahapan pekerjaan yang disepakati di kontrak, bukan sekaligus di awal.','Biaya','published',3,1,1,'2026-11-24 07:14:10','2026-11-24 07:14:10'),
(4,'Berapa lama pengerjaan?','Setelah survei, kami berikan jadwal tertulis per tahapan. Lamanya bergantung pada lingkup dan kondisi bangunan.','Proses kerja','published',4,1,1,'2026-11-24 07:14:10','2026-11-24 07:14:10'),
(5,'Bisakah rumah tetap ditempati selama renovasi?','Bisa untuk banyak kasus. Pekerjaan dibagi per zona supaya sebagian ruang tetap bisa dipakai. Hal ini kami bahas saat survei.','Proses kerja','published',5,1,1,'2026-11-24 07:14:10','2026-11-24 07:14:10'),
(6,'Bagaimana kalau hasil pekerjaan tidak sesuai?','Kami memeriksa hasil bersama Anda saat serah terima. Kekurangan yang ditemukan dicatat dan diperbaiki sebelum pekerjaan dinyatakan selesai.','','published',6,1,1,'2026-11-24 07:14:10','2026-11-24 07:14:10');
/*!40000 ALTER TABLE `faqs` ENABLE KEYS */;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;


SET FOREIGN_KEY_CHECKS = 1;
SET @admin = (SELECT MIN(id) FROM users WHERE role_id = 1);
UPDATE media SET uploaded_by = @admin WHERE uploaded_by IS NOT NULL;
UPDATE pages SET created_by = @admin, updated_by = @admin, updated_at = updated_at;
UPDATE services SET created_by = @admin, updated_by = @admin, updated_at = updated_at;
UPDATE projects SET created_by = @admin, updated_by = @admin, updated_at = updated_at;
UPDATE testimonials SET created_by = @admin, updated_by = @admin, updated_at = updated_at;
UPDATE faqs SET created_by = @admin, updated_by = @admin, updated_at = updated_at;
UPDATE articles SET author_id = @admin, created_by = @admin, updated_by = @admin, updated_at = updated_at;
