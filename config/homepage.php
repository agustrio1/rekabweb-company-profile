<?php

declare(strict_types=1);

/*
 * Schema section beranda. Urutan di sini = urutan awal sebelum admin menyimpan.
 * Tipe field: text, textarea, link, image, select (sama seperti config/settings.php).
 * 'repeater' = daftar item (min dihitung hanya saat section aktif).
 * 'source'   = isi daftar diambil dari modul lain (PHASE 7).
 */
$counts = ['3' => '3 item', '4' => '4 item', '6' => '6 item'];

return [
    'sections' => [
        'hero' => [
            'label' => 'Hero',
            'description' => 'Bagian paling atas: judul utama, tombol, dan gambar.',
            'fields' => [
                'eyebrow' => ['label' => 'Teks kecil di atas judul', 'type' => 'text', 'max' => 60, 'default' => ''],
                'headline' => [
                    'label' => 'Judul utama', 'type' => 'text', 'max' => 120, 'required' => true, 'wide' => true,
                    'default' => 'Layanan profesional untuk kebutuhan bisnis Anda',
                ],
                'description' => [
                    'label' => 'Deskripsi', 'type' => 'textarea', 'max' => 300, 'wide' => true,
                    'default' => 'Ceritakan kebutuhan Anda. Tim kami menjawab dengan rencana kerja yang jelas, biaya yang transparan, dan jadwal yang bisa dipegang.',
                ],
                'cta_label' => ['label' => 'Tombol utama', 'type' => 'text', 'max' => 40, 'default' => 'Konsultasi Gratis'],
                'cta_url' => ['label' => 'Tujuan tombol utama', 'type' => 'link', 'max' => 200, 'default' => '/kontak'],
                'secondary_label' => ['label' => 'Tombol kedua', 'type' => 'text', 'max' => 40, 'default' => 'Lihat layanan'],
                'secondary_url' => ['label' => 'Tujuan tombol kedua', 'type' => 'link', 'max' => 200, 'default' => '/layanan'],
                'image' => [
                    'label' => 'Gambar hero', 'type' => 'image', 'max' => 10,
                    'hint' => 'Disarankan landscape, minimal lebar 1200 px.',
                ],
            ],
        ],

        'about' => [
            'label' => 'Ringkasan perusahaan',
            'description' => 'Pengenalan singkat dengan tautan ke halaman profil.',
            'fields' => [
                'title' => ['label' => 'Judul', 'type' => 'text', 'max' => 120, 'required' => true, 'wide' => true, 'default' => 'Tentang perusahaan'],
                'text' => ['label' => 'Teks', 'type' => 'textarea', 'max' => 600, 'wide' => true, 'default' => ''],
                'link_label' => ['label' => 'Teks tautan', 'type' => 'text', 'max' => 40, 'default' => 'Selengkapnya tentang kami'],
                'link_url' => ['label' => 'Tujuan tautan', 'type' => 'link', 'max' => 200, 'default' => '/tentang'],
                'image' => ['label' => 'Gambar', 'type' => 'image', 'max' => 10],
            ],
        ],

        'services' => [
            'label' => 'Layanan unggulan',
            'description' => 'Menampilkan layanan dari modul Layanan.',
            'source' => 'Layanan',
            'fields' => [
                'title' => ['label' => 'Judul', 'type' => 'text', 'max' => 120, 'required' => true, 'default' => 'Layanan kami'],
                'subtitle' => ['label' => 'Subjudul', 'type' => 'text', 'max' => 200, 'default' => 'Layanan yang paling sering diminta klien.'],
                'count' => ['label' => 'Jumlah tampil', 'type' => 'select', 'default' => '3', 'options' => $counts],
            ],
        ],

        'advantages' => [
            'label' => 'Keunggulan',
            'description' => 'Alasan calon klien memilih perusahaan Anda.',
            'fields' => [
                'title' => ['label' => 'Judul', 'type' => 'text', 'max' => 120, 'required' => true, 'default' => 'Mengapa memilih kami'],
                'subtitle' => ['label' => 'Subjudul', 'type' => 'text', 'max' => 200, 'default' => ''],
            ],
            'repeater' => [
                'label' => 'Daftar keunggulan',
                'item_label' => 'Keunggulan',
                'max' => 6,
                'min' => 1,
                'hint' => 'Tulis hal yang benar-benar Anda lakukan, bukan klaim umum.',
                'fields' => [
                    'title' => ['label' => 'Judul', 'type' => 'text', 'max' => 60, 'required' => true],
                    'text' => ['label' => 'Penjelasan', 'type' => 'textarea', 'max' => 200],
                ],
                'default' => [
                    ['title' => 'Rencana kerja tertulis', 'text' => 'Setiap pekerjaan dimulai dari lingkup, jadwal, dan biaya yang disepakati bersama.'],
                    ['title' => 'Satu pintu komunikasi', 'text' => 'Anda berbicara dengan penanggung jawab proyek yang sama dari awal sampai selesai.'],
                    ['title' => 'Hasil yang bisa diperiksa', 'text' => 'Progres dilaporkan berkala dan hasil akhir diserahterimakan bersama dokumennya.'],
                ],
            ],
        ],

        'stats' => [
            'label' => 'Statistik',
            'description' => 'Angka pencapaian. Isi hanya dengan angka yang bisa dibuktikan.',
            'default_active' => false,
            'fields' => [
                'title' => ['label' => 'Judul (opsional)', 'type' => 'text', 'max' => 120, 'default' => ''],
            ],
            'repeater' => [
                'label' => 'Daftar angka',
                'item_label' => 'Angka',
                'max' => 4,
                'min' => 1,
                'hint' => 'Contoh: nilai "12" dengan label "Tahun beroperasi".',
                'fields' => [
                    'value' => ['label' => 'Angka', 'type' => 'text', 'max' => 20, 'required' => true],
                    'label' => ['label' => 'Keterangan', 'type' => 'text', 'max' => 60, 'required' => true],
                ],
                'default' => [],
            ],
        ],

        'portfolio' => [
            'label' => 'Portofolio unggulan',
            'description' => 'Menampilkan proyek dari modul Portofolio.',
            'source' => 'Portofolio',
            'fields' => [
                'title' => ['label' => 'Judul', 'type' => 'text', 'max' => 120, 'required' => true, 'default' => 'Proyek terpilih'],
                'subtitle' => ['label' => 'Subjudul', 'type' => 'text', 'max' => 200, 'default' => ''],
                'count' => ['label' => 'Jumlah tampil', 'type' => 'select', 'default' => '3', 'options' => $counts],
            ],
        ],

        'process' => [
            'label' => 'Proses kerja',
            'description' => 'Langkah kerja dari konsultasi sampai selesai.',
            'fields' => [
                'title' => ['label' => 'Judul', 'type' => 'text', 'max' => 120, 'required' => true, 'default' => 'Cara kami bekerja'],
                'subtitle' => ['label' => 'Subjudul', 'type' => 'text', 'max' => 200, 'default' => ''],
            ],
            'repeater' => [
                'label' => 'Daftar langkah',
                'item_label' => 'Langkah',
                'max' => 6,
                'min' => 1,
                'fields' => [
                    'title' => ['label' => 'Judul langkah', 'type' => 'text', 'max' => 60, 'required' => true],
                    'text' => ['label' => 'Penjelasan', 'type' => 'textarea', 'max' => 200],
                ],
                'default' => [
                    ['title' => 'Konsultasi', 'text' => 'Kami mendengar kebutuhan dan kondisi lapangan Anda.'],
                    ['title' => 'Penawaran', 'text' => 'Anda menerima lingkup kerja, jadwal, dan rincian biaya tertulis.'],
                    ['title' => 'Pengerjaan', 'text' => 'Pekerjaan dijalankan sesuai rencana dengan laporan progres.'],
                    ['title' => 'Serah terima', 'text' => 'Hasil diperiksa bersama dan diserahkan lengkap dengan dokumennya.'],
                ],
            ],
        ],

        'testimonials' => [
            'label' => 'Testimoni',
            'description' => 'Menampilkan testimoni dari modul Testimoni.',
            'source' => 'Testimoni',
            'fields' => [
                'title' => ['label' => 'Judul', 'type' => 'text', 'max' => 120, 'required' => true, 'default' => 'Kata klien'],
                'subtitle' => ['label' => 'Subjudul', 'type' => 'text', 'max' => 200, 'default' => ''],
                'count' => ['label' => 'Jumlah tampil', 'type' => 'select', 'default' => '3', 'options' => $counts],
            ],
        ],

        'faq' => [
            'label' => 'FAQ',
            'description' => 'Menampilkan pertanyaan dari modul FAQ.',
            'source' => 'FAQ',
            'fields' => [
                'title' => ['label' => 'Judul', 'type' => 'text', 'max' => 120, 'required' => true, 'default' => 'Pertanyaan umum'],
                'subtitle' => ['label' => 'Subjudul', 'type' => 'text', 'max' => 200, 'default' => ''],
                'count' => ['label' => 'Jumlah tampil', 'type' => 'select', 'default' => '4', 'options' => $counts],
            ],
        ],

        'cta' => [
            'label' => 'Ajakan konsultasi (CTA)',
            'description' => 'Blok ajakan menghubungi sebelum footer.',
            'fields' => [
                'title' => ['label' => 'Judul', 'type' => 'text', 'max' => 120, 'required' => true, 'wide' => true, 'default' => 'Siap berdiskusi tentang kebutuhan Anda?'],
                'text' => ['label' => 'Teks', 'type' => 'textarea', 'max' => 300, 'wide' => true, 'default' => 'Sampaikan kebutuhan Anda dan kami balas dengan langkah berikutnya.'],
                'button_label' => ['label' => 'Teks tombol', 'type' => 'text', 'max' => 40, 'default' => 'Konsultasi Gratis'],
                'button_url' => ['label' => 'Tujuan tombol', 'type' => 'link', 'max' => 200, 'default' => '/kontak'],
            ],
        ],

        'articles' => [
            'label' => 'Artikel terbaru',
            'description' => 'Menampilkan artikel dari modul Blog.',
            'source' => 'Artikel',
            'fields' => [
                'title' => ['label' => 'Judul', 'type' => 'text', 'max' => 120, 'required' => true, 'default' => 'Artikel terbaru'],
                'subtitle' => ['label' => 'Subjudul', 'type' => 'text', 'max' => 200, 'default' => ''],
                'count' => ['label' => 'Jumlah tampil', 'type' => 'select', 'default' => '3', 'options' => $counts],
            ],
        ],
    ],
];