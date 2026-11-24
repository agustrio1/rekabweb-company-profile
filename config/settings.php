<?php

declare(strict_types=1);

/*
 * Schema pengaturan website. Tipe: text, textarea, email, phone, whatsapp,
 * url, link (path internal atau URL), select, code.
 */
return [
    'groups' => [
        'company' => [
            'label' => 'Perusahaan',
            'fields' => [
                'company_name' => [
                    'label' => 'Nama perusahaan', 'type' => 'text', 'max' => 100, 'required' => true,
                    'default' => (string) env('APP_NAME', 'Company Profile'),
                ],
                'tagline' => [
                    'label' => 'Tagline', 'type' => 'text', 'max' => 150,
                    'hint' => 'Kalimat singkat di bawah nama perusahaan.',
                ],
                'logo' => [
                    'label' => 'Logo', 'type' => 'image', 'max' => 10,
                    'hint' => 'Disarankan PNG atau WebP dengan latar transparan.',
                ],
                'favicon' => [
                    'label' => 'Favicon', 'type' => 'image', 'max' => 10,
                    'hint' => 'Gambar persegi, minimal 64×64 px.',
                ],
                'description' => [
                    'label' => 'Deskripsi singkat', 'type' => 'textarea', 'max' => 500, 'wide' => true,
                    'hint' => 'Dipakai di footer dan sebagai cadangan meta description.',
                ],
                'address' => [
                    'label' => 'Alamat', 'type' => 'textarea', 'max' => 300, 'wide' => true,
                ],
                'phone' => [
                    'label' => 'Nomor telepon', 'type' => 'phone', 'max' => 30,
                    'placeholder' => '021 1234 5678',
                ],
                'whatsapp' => [
                    'label' => 'Nomor WhatsApp', 'type' => 'whatsapp', 'max' => 25,
                    'placeholder' => '0812 3456 7890',
                    'hint' => 'Tulis 08… atau 62…, disimpan sebagai 62….',
                ],
                'email' => [
                    'label' => 'Email', 'type' => 'email', 'max' => 190,
                    'placeholder' => 'halo@perusahaan.com',
                ],
                'business_hours' => [
                    'label' => 'Jam operasional', 'type' => 'textarea', 'max' => 200, 'wide' => true,
                    'placeholder' => "Senin - Jumat: 08.00 - 17.00\nSabtu: 08.00 - 13.00",
                ],
            ],
        ],

        'social' => [
            'label' => 'Social media',
            'fields' => [
                'instagram' => ['label' => 'Instagram', 'type' => 'url', 'max' => 255, 'placeholder' => 'https://instagram.com/akun'],
                'facebook' => ['label' => 'Facebook', 'type' => 'url', 'max' => 255, 'placeholder' => 'https://facebook.com/halaman'],
                'linkedin' => ['label' => 'LinkedIn', 'type' => 'url', 'max' => 255, 'placeholder' => 'https://linkedin.com/company/nama'],
                'youtube' => ['label' => 'YouTube', 'type' => 'url', 'max' => 255, 'placeholder' => 'https://youtube.com/@kanal'],
                'tiktok' => ['label' => 'TikTok', 'type' => 'url', 'max' => 255, 'placeholder' => 'https://tiktok.com/@akun'],
            ],
        ],

        'seo' => [
            'label' => 'SEO',
            'fields' => [
                'seo_title' => [
                    'label' => 'Meta title default', 'type' => 'text', 'max' => 70,
                    'hint' => 'Maksimal 70 karakter. Dipakai kalau halaman tidak punya judul SEO sendiri.',
                ],
                'seo_description' => [
                    'label' => 'Meta description default', 'type' => 'textarea', 'max' => 160, 'wide' => true,
                    'hint' => 'Maksimal 160 karakter.',
                ],
                'og_image' => [
                    'label' => 'Gambar OG default', 'type' => 'image', 'max' => 10,
                    'hint' => 'Tampil saat link dibagikan di WhatsApp dan sosial media. Ideal 1200×630 px.',
                ],
                'robots' => [
                    'label' => 'Mesin pencari', 'type' => 'select', 'default' => 'index',
                    'options' => [
                        'index' => 'Izinkan Google mengindeks website',
                        'noindex' => 'Blokir pengindeksan (saat website belum siap)',
                    ],
                ],
                'google_verification' => [
                    'label' => 'Kode verifikasi Google', 'type' => 'code', 'max' => 100,
                    'hint' => 'Isi hanya kodenya (isi atribut content), bukan seluruh tag meta.',
                ],
            ],
        ],

        'website' => [
            'label' => 'Website',
            'fields' => [
                'cta_label' => [
                    'label' => 'Teks tombol CTA', 'type' => 'text', 'max' => 40, 'default' => 'Konsultasi Gratis',
                ],
                'cta_url' => [
                    'label' => 'Tujuan tombol CTA', 'type' => 'link', 'max' => 200, 'default' => '/kontak',
                    'hint' => 'Path internal (contoh /kontak) atau URL lengkap https://…',
                ],
                'footer_text' => [
                    'label' => 'Teks footer', 'type' => 'textarea', 'max' => 300, 'wide' => true,
                ],
                'copyright' => [
                    'label' => 'Teks hak cipta', 'type' => 'text', 'max' => 150,
                    'default' => 'Seluruh hak cipta dilindungi.',
                    'hint' => 'Tanda © dan tahun otomatis ditambahkan di footer.',
                ],
            ],
        ],
    ],
];