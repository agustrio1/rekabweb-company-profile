<?php

declare(strict_types=1);

// Data demo perusahaan fiktif. Semua isi di sini fiktif, ganti sebelum dipakai klien sungguhan.

$p = static fn (string ...$t): string => implode('', array_map(static fn (string $x): string => '<p>' . $x . '</p>', $t));
$h = static fn (string $t): string => '<h2>' . $t . '</h2>';
$ul = static fn (array $i): string => '<ul>' . implode('', array_map(static fn (string $x): string => '<li>' . $x . '</li>', $i)) . '</ul>';

return [
    // key => [lebar, tinggi, alt text]
    'images' => [
        'hero' => [1200, 1500, 'Ilustrasi contoh untuk bagian hero'],
        'about' => [1600, 1200, 'Ilustrasi contoh untuk profil perusahaan'],
        'og' => [1200, 630, 'Gambar contoh untuk pratinjau tautan'],
        'favicon' => [256, 256, 'Ikon situs'],
        'svc1' => [1600, 1000, 'Ilustrasi contoh layanan renovasi'],
        'svc2' => [1600, 1000, 'Ilustrasi contoh layanan pembangunan'],
        'svc3' => [1600, 1000, 'Ilustrasi contoh layanan interior'],
        'svc4' => [1600, 1000, 'Ilustrasi contoh layanan perawatan gedung'],
        'prj1' => [1600, 1000, 'Ilustrasi contoh proyek renovasi rumah'],
        'prj2' => [1600, 1000, 'Ilustrasi contoh proyek perluasan ruko'],
        'prj3' => [1600, 1000, 'Ilustrasi contoh proyek interior kantor'],
        'prj4' => [1600, 1000, 'Ilustrasi contoh proyek gudang'],
        'prj5' => [1600, 1000, 'Ilustrasi contoh proyek dapur'],
        'prj6' => [1600, 1000, 'Ilustrasi contoh proyek kafe'],
        'g1a' => [1600, 1200, 'Ilustrasi galeri proyek 1, tampak depan'],
        'g1b' => [1600, 1200, 'Ilustrasi galeri proyek 1, ruang keluarga'],
        'g1c' => [1600, 1200, 'Ilustrasi galeri proyek 1, dapur'],
        'g2a' => [1600, 1200, 'Ilustrasi galeri proyek 2, lantai satu'],
        'g2b' => [1600, 1200, 'Ilustrasi galeri proyek 2, lantai dua'],
        'art1' => [1600, 1000, 'Ilustrasi artikel tentang RAB'],
        'art2' => [1600, 1000, 'Ilustrasi artikel tentang survei lokasi'],
        'art3' => [1600, 1000, 'Ilustrasi artikel tentang perizinan'],
        'art4' => [1600, 1000, 'Ilustrasi artikel tentang material lantai'],
        'art5' => [1600, 1000, 'Ilustrasi artikel tentang dapur kecil'],
        'art6' => [1600, 1000, 'Ilustrasi artikel tentang atap bocor'],
    ],

    'settings' => [
        'company_name' => 'Karya Mandala',
        'tagline' => 'Renovasi, pembangunan, dan interior untuk rumah dan usaha',
        'description' => 'Karya Mandala adalah kontraktor renovasi, pembangunan, dan interior di Bandung. Kami mengerjakan rumah tinggal, ruko, dan kantor dengan rencana kerja tertulis dan laporan progres berkala.',
        'address' => "Jl. Contoh Raya No. 12\nKota Bandung, Jawa Barat 40100",
        'phone' => '022 1234 5678',
        'whatsapp' => '6281234567890',
        'email' => 'halo@karyamandala.example',
        'business_hours' => "Senin - Jumat: 08.00 - 17.00\nSabtu: 08.00 - 13.00",
        'seo_title' => 'Karya Mandala - Kontraktor Renovasi dan Interior Bandung',
        'seo_description' => 'Kontraktor renovasi, pembangunan, dan interior di Bandung. Rencana kerja tertulis, biaya dirinci, dan laporan progres berkala. Konsultasi awal gratis.',
        'robots' => 'noindex',
        'cta_label' => 'Konsultasi Gratis',
        'cta_url' => '/kontak',
        'footer_text' => '',
        'copyright' => 'Seluruh hak cipta dilindungi.',
    ],

    'homepage' => [
        'hero' => [
            'eyebrow' => 'Kontraktor renovasi dan interior di Bandung',
            'headline' => 'Renovasi dan pembangunan yang selesai sesuai rencana',
            'description' => 'Ceritakan kebutuhan Anda. Kami menjawab dengan rencana kerja tertulis, rincian biaya, dan jadwal yang bisa dipegang, lalu melaporkan progres sampai serah terima.',
            'image' => '@hero',
        ],
        'about' => [
            'title' => 'Tentang Karya Mandala',
            'text' => "Kami adalah tim kecil yang mengerjakan renovasi, pembangunan, dan interior untuk rumah tinggal, ruko, dan kantor di Bandung Raya.\n\nSetiap proyek dipegang satu penanggung jawab, dari survei sampai serah terima, supaya Anda tidak perlu menjelaskan hal yang sama berulang kali.",
            'image' => '@about',
        ],
        'advantages' => [
            'items' => [
                ['title' => 'Rencana kerja tertulis', 'text' => 'Lingkup, jadwal, dan rincian biaya disepakati tertulis sebelum pekerjaan dimulai.'],
                ['title' => 'Satu penanggung jawab', 'text' => 'Anda berhubungan dengan orang yang sama dari survei sampai serah terima.'],
                ['title' => 'Laporan progres berkala', 'text' => 'Foto dan catatan perkembangan dikirim rutin, jadi Anda tidak perlu menebak-nebak.'],
                ['title' => 'Biaya yang dirinci', 'text' => 'Material, upah, dan pekerjaan tambahan dijelaskan per item, tanpa angka gelondongan.'],
            ],
        ],
    ],

    'pages' => [
        [
            'title' => 'Tentang Kami',
            'slug' => 'tentang',
            'description' => 'Mengenal Karya Mandala, kontraktor renovasi, pembangunan, dan interior di Bandung.',
            'content' => $h('Siapa kami')
                . $p('Karya Mandala adalah kontraktor renovasi, pembangunan, dan interior yang berbasis di Bandung. Kami mengerjakan rumah tinggal, ruko, dan kantor, dari perbaikan ringan sampai perubahan tata ruang.', 'Kami memilih tetap berukuran kecil supaya setiap proyek mendapat perhatian langsung dari orang yang bertanggung jawab atasnya.')
                . $h('Visi dan misi')
                . $p('Visi kami sederhana: menjadi kontraktor yang dipercaya karena pekerjaannya terbuka dan bisa diperiksa.')
                . $ul(['Menjelaskan lingkup, jadwal, dan biaya secara tertulis sebelum pekerjaan dimulai.', 'Melaporkan progres secara berkala dengan foto dan catatan.', 'Menyerahkan hasil pekerjaan setelah diperiksa bersama pemilik.'])
                . $h('Nilai kami')
                . $ul(['<strong>Terbuka.</strong> Kami menjelaskan apa yang dikerjakan dan mengapa biayanya demikian.', '<strong>Tepat waktu.</strong> Jadwal disusun dari kemampuan nyata tim, bukan dari janji.', '<strong>Rapi.</strong> Area kerja dijaga bersih supaya aktivitas Anda tetap berjalan.', '<strong>Bertanggung jawab.</strong> Kekurangan pekerjaan yang ditemukan saat serah terima kami perbaiki.'])
                . $h('Cara kami bekerja')
                . $p('Kami mulai dari survei lokasi dan mendengarkan kebutuhan Anda. Dari situ kami menyusun gambar kerja dan rencana anggaran biaya, lalu mengerjakannya bertahap dengan laporan progres. Di akhir, kami memeriksa hasilnya bersama Anda sebelum serah terima.'),
        ],
        [
            'title' => 'Kebijakan Privasi',
            'slug' => 'kebijakan-privasi',
            'description' => 'Data apa yang dikumpulkan website Karya Mandala dan bagaimana data itu digunakan.',
            'content' => $p('Kebijakan ini menjelaskan data yang kami kumpulkan lewat website ini dan cara kami menggunakannya. Teks ini adalah templat umum dan perlu ditinjau sesuai kebutuhan usaha Anda.')
                . $h('Data yang kami kumpulkan')
                . $p('Data hanya kami terima ketika Anda mengirim formulir kontak: nama, email, nomor WhatsApp, nama perusahaan (jika diisi), jenis kebutuhan, dan isi pesan. Untuk mencegah spam, alamat IP pengirim juga tercatat bersama pesan.')
                . $h('Cara kami menggunakan data')
                . $ul(['Membalas pesan dan menindaklanjuti kebutuhan Anda.', 'Mencatat riwayat komunikasi agar tidak ada permintaan yang terlewat.', 'Mencegah penyalahgunaan formulir.'])
                . $p('Kami tidak menjual atau membagikan data Anda kepada pihak lain untuk keperluan pemasaran.')
                . $h('Cookie dan pelacakan')
                . $p('Halaman publik website ini tidak memasang cookie pelacak dan tidak memakai layanan analitik pihak ketiga.')
                . $h('Penyimpanan dan penghapusan')
                . $p('Pesan Anda disimpan selama masih diperlukan untuk menindaklanjuti kebutuhan Anda. Anda dapat meminta data Anda dihapus dengan menghubungi kami lewat kontak di halaman Kontak.')
                . $h('Perubahan kebijakan')
                . $p('Kebijakan ini dapat diperbarui sewaktu-waktu. Versi terbaru selalu tersedia di halaman ini.'),
        ],
        [
            'title' => 'Syarat dan Ketentuan',
            'slug' => 'syarat-ketentuan',
            'description' => 'Ketentuan penggunaan website Karya Mandala.',
            'content' => $p('Dengan menggunakan website ini, Anda menyetujui ketentuan berikut. Teks ini adalah templat umum dan perlu ditinjau sesuai kebutuhan usaha Anda.')
                . $h('Penggunaan website')
                . $p('Informasi di website ini bersifat umum dan dapat berubah tanpa pemberitahuan. Isi website bukan penawaran yang mengikat. Penawaran resmi hanya berlaku bila diberikan tertulis oleh kami.')
                . $h('Layanan dan penawaran')
                . $ul(['Lingkup pekerjaan, jadwal, dan biaya ditetapkan dalam penawaran atau kontrak tertulis.', 'Perubahan lingkup setelah pekerjaan dimulai dibicarakan dan disepakati terlebih dahulu.'])
                . $h('Hak cipta')
                . $p('Teks, gambar, dan desain di website ini dimiliki Karya Mandala kecuali dinyatakan lain. Penggunaan ulang memerlukan izin tertulis.')
                . $h('Batasan tanggung jawab')
                . $p('Kami berupaya menjaga keakuratan informasi di website ini, tetapi tidak menjamin bahwa seluruh isinya selalu lengkap dan terbaru.')
                . $h('Kontak')
                . $p('Pertanyaan tentang ketentuan ini dapat disampaikan lewat halaman Kontak.'),
        ],
    ],

    'services' => [
        [
            'title' => 'Renovasi Rumah dan Ruko', 'slug' => 'renovasi-rumah-dan-ruko', 'image' => 'svc1', 'featured' => true,
            'summary' => 'Perbaikan dan penataan ulang rumah, ruko, atau kantor, dari pekerjaan ringan sampai perubahan tata ruang.',
            'content' => $h('Apa yang kami kerjakan')
                . $p('Kami menangani renovasi menyeluruh maupun per bagian. Setiap pekerjaan dimulai dari survei lokasi supaya rencana dan anggaran didasarkan pada kondisi bangunan yang sebenarnya.')
                . $ul(['Perubahan tata ruang dan penambahan ruang.', 'Pekerjaan dinding, lantai, plafon, dan atap.', 'Perbaikan instalasi listrik dan air.', 'Pengecatan dan finishing.'])
                . $h('Cocok untuk')
                . $ul(['Pemilik rumah yang ingin menata ulang ruang.', 'Pemilik ruko yang perlu menyesuaikan tampilan dan fungsi usaha.', 'Kantor kecil yang butuh ruang kerja lebih tertata.']),
            'benefits' => [
                ['title' => 'Biaya dirinci per pekerjaan', 'text' => 'Anda tahu ke mana setiap rupiah dipakai dan bisa memilih mana yang didahulukan.'],
                ['title' => 'Jadwal tertulis', 'text' => 'Tahapan dan target selesai disepakati di awal dan dilaporkan setiap pekan.'],
                ['title' => 'Area kerja tetap rapi', 'text' => 'Debu dan material dikelola supaya aktivitas rumah atau usaha tetap berjalan.'],
            ],
            'process' => [
                ['title' => 'Survei lokasi', 'text' => 'Kami memeriksa kondisi bangunan dan mencatat kebutuhan Anda.'],
                ['title' => 'Gambar kerja dan RAB', 'text' => 'Anda menerima gambar dan rincian biaya untuk disetujui.'],
                ['title' => 'Pengerjaan bertahap', 'text' => 'Pekerjaan dijalankan sesuai jadwal dengan laporan progres.'],
                ['title' => 'Serah terima', 'text' => 'Hasil diperiksa bersama dan kekurangan diperbaiki sebelum selesai.'],
            ],
            'faqs' => [
                ['question' => 'Apakah survei lokasi berbayar?', 'answer' => 'Survei awal di wilayah Bandung Raya tidak dipungut biaya. Untuk lokasi di luar wilayah itu, kami informasikan dulu kalau ada biaya transportasi.'],
                ['question' => 'Berapa lama renovasi berlangsung?', 'answer' => 'Tergantung lingkup pekerjaan. Setelah survei, kami berikan jadwal tertulis per tahapan, dan itu yang kami laporkan progresnya.'],
            ],
        ],
        [
            'title' => 'Pembangunan Baru', 'slug' => 'pembangunan-baru', 'image' => 'svc2', 'featured' => true,
            'summary' => 'Pembangunan rumah, ruko, gudang kecil, dan bangunan usaha dari gambar kerja sampai serah terima.',
            'content' => $h('Dari gambar sampai bangunan jadi')
                . $p('Kami membangun dengan gambar kerja dan anggaran yang jelas. Pemilik dilibatkan pada keputusan penting seperti pemilihan material dan perubahan desain.')
                . $ul(['Rumah tinggal satu sampai dua lantai.', 'Ruko dan bangunan usaha kecil.', 'Gudang dan ruang penyimpanan sederhana.'])
                . $h('Yang perlu Anda siapkan')
                . $p('Kepemilikan lahan yang jelas dan gambaran kebutuhan ruang. Soal gambar kerja dan perhitungan biaya, kami bantu dari sana.'),
            'benefits' => [
                ['title' => 'Anggaran terukur', 'text' => 'RAB disusun per tahapan sehingga arus biaya mudah dipantau.'],
                ['title' => 'Pengawasan di lapangan', 'text' => 'Ada penanggung jawab yang rutin memeriksa mutu pekerjaan.'],
                ['title' => 'Dokumen serah terima', 'text' => 'Gambar akhir dan catatan pekerjaan diserahkan bersama bangunan.'],
            ],
            'process' => [
                ['title' => 'Konsultasi kebutuhan', 'text' => 'Kami memahami kebutuhan ruang, anggaran, dan jadwal.'],
                ['title' => 'Perencanaan', 'text' => 'Gambar kerja dan RAB disusun lalu disetujui bersama.'],
                ['title' => 'Konstruksi', 'text' => 'Pekerjaan dijalankan per tahap dengan laporan progres.'],
                ['title' => 'Pemeriksaan akhir', 'text' => 'Bangunan diperiksa bersama sebelum diserahkan.'],
            ],
            'faqs' => [
                ['question' => 'Apakah Karya Mandala membantu perizinan bangunan?', 'answer' => 'Kami bisa membantu menyiapkan dokumen teknis. Proses pengajuannya mengikuti ketentuan pemerintah daerah setempat, dan kami jelaskan langkahnya saat konsultasi.'],
                ['question' => 'Bisakah pembayaran dilakukan bertahap?', 'answer' => 'Bisa. Pembayaran dikaitkan dengan tahapan pekerjaan yang disepakati di kontrak.'],
            ],
        ],
        [
            'title' => 'Desain dan Interior', 'slug' => 'desain-dan-interior', 'image' => 'svc3', 'featured' => true,
            'summary' => 'Perencanaan dan pengerjaan interior rumah, kafe, dan kantor yang fungsional dan sesuai anggaran.',
            'content' => $h('Interior yang fungsional')
                . $p('Kami merancang ruang dari cara Anda memakainya. Desain ditampilkan dalam gambar sebelum pengerjaan, dan material dipilih sesuai anggaran serta kebutuhan perawatan.')
                . $ul(['Furnitur custom: lemari, meja kerja, dan kabinet dapur.', 'Penataan pencahayaan dan sirkulasi ruang.', 'Pemilihan material dan palet warna.']),
            'benefits' => [
                ['title' => 'Gambar sebelum dikerjakan', 'text' => 'Anda bisa menilai rancangan sebelum biaya produksi dikeluarkan.'],
                ['title' => 'Material sesuai anggaran', 'text' => 'Kami memberi pilihan dengan perbandingan harga dan perawatan.'],
                ['title' => 'Pengerjaan terkoordinasi', 'text' => 'Desain dan produksi dipegang satu tim, sehingga hasil sesuai rancangan.'],
            ],
            'process' => [
                ['title' => 'Brief dan ukur ruang', 'text' => 'Kami mencatat kebutuhan dan mengukur ruang yang ada.'],
                ['title' => 'Konsep dan gambar', 'text' => 'Anda menerima rancangan untuk ditinjau dan direvisi.'],
                ['title' => 'Produksi dan pemasangan', 'text' => 'Furnitur dibuat dan dipasang sesuai gambar yang disetujui.'],
                ['title' => 'Finishing dan serah terima', 'text' => 'Detail akhir dirapikan dan diperiksa bersama.'],
            ],
            'faqs' => [
                ['question' => 'Apakah desain bisa dipesan terpisah dari pengerjaan?', 'answer' => 'Bisa. Anda boleh memesan desain saja, atau melanjutkan ke pengerjaan oleh tim kami.'],
            ],
        ],
        [
            'title' => 'Perawatan dan Perbaikan Gedung', 'slug' => 'perawatan-dan-perbaikan-gedung', 'image' => 'svc4', 'featured' => false,
            'summary' => 'Perawatan berkala dan perbaikan cepat untuk atap, saluran, dinding, dan instalasi bangunan.',
            'content' => $h('Mencegah kerusakan kecil jadi besar')
                . $p('Banyak kerusakan mahal bermula dari masalah kecil yang dibiarkan. Kami memeriksa kondisi bangunan dan memperbaiki yang perlu sebelum meluas.')
                . $ul(['Perbaikan atap bocor dan talang.', 'Perbaikan dinding lembap dan retak rambut.', 'Pengecekan instalasi listrik dan air.', 'Pengecatan ulang area terdampak.']),
            'benefits' => [
                ['title' => 'Pemeriksaan dengan catatan', 'text' => 'Temuan dicatat dan diurutkan dari yang paling mendesak.'],
                ['title' => 'Perbaikan tepat sasaran', 'text' => 'Kami mengerjakan penyebabnya, bukan hanya menutup gejalanya.'],
            ],
            'process' => [
                ['title' => 'Pemeriksaan', 'text' => 'Kami memeriksa area yang bermasalah.'],
                ['title' => 'Usulan perbaikan', 'text' => 'Anda menerima rincian pekerjaan dan biaya.'],
                ['title' => 'Perbaikan', 'text' => 'Pekerjaan dilakukan dengan area kerja yang dijaga rapi.'],
            ],
            'faqs' => [],
        ],
    ],

    'project_categories' => ['Renovasi', 'Pembangunan', 'Interior'],

    'projects' => [
        [
            'title' => 'Renovasi Rumah Tinggal Dago', 'slug' => 'renovasi-rumah-tinggal-dago', 'category' => 'Renovasi',
            'summary' => 'Penataan ulang rumah dua lantai agar ruang keluarga, dapur, dan teras menyatu dan lebih terang.',
            'client' => 'Keluarga Hartono', 'show_client' => false, 'location' => 'Bandung, Jawa Barat', 'year' => 2025,
            'image' => 'prj1', 'gallery' => ['g1a', 'g1b', 'g1c'], 'featured' => true, 'days' => 10,
            'content' => $p('Rumah berusia puluhan tahun ini direnovasi untuk menyesuaikan kebutuhan keluarga yang bertambah. Pekerjaan mencakup perubahan tata ruang lantai satu, penggantian lantai, dan penataan ulang dapur.'),
            'challenge' => 'Ruang keluarga, dapur, dan ruang makan terpisah dinding sehingga rumah terasa gelap dan sempit. Pemilik tetap tinggal di rumah selama pekerjaan berlangsung.',
            'solution' => "Dinding non-struktural dibongkar setelah pemeriksaan, lalu ruang dibuka menjadi satu area. Pekerjaan dibagi per zona supaya sebagian rumah tetap bisa dipakai, dan debu dibatasi dengan sekat sementara.",
            'results' => 'Ruang keluarga dan dapur menyatu dengan cahaya alami yang lebih baik. Pekerjaan diserahkan setelah diperiksa bersama pemilik dan beberapa catatan finishing diperbaiki.',
        ],
        [
            'title' => 'Perluasan Ruko Dua Lantai', 'slug' => 'perluasan-ruko-dua-lantai', 'category' => 'Renovasi',
            'summary' => 'Penambahan ruang usaha di lantai dua dan penataan ulang etalase ruko di kawasan perdagangan.',
            'client' => 'Toko Sinar Pagi', 'show_client' => true, 'location' => 'Cimahi, Jawa Barat', 'year' => 2024,
            'image' => 'prj2', 'gallery' => ['g2a', 'g2b'], 'featured' => true, 'days' => 20,
            'content' => $p('Pemilik usaha membutuhkan ruang penyimpanan dan area layanan tambahan tanpa menghentikan operasional toko.'),
            'challenge' => 'Toko harus tetap buka selama renovasi, dan akses material hanya lewat satu pintu depan.',
            'solution' => 'Pekerjaan berisik dijadwalkan di luar jam ramai. Material diangkut pada jam tertentu dan area etalase ditutup sementara per bagian.',
            'results' => 'Toko memiliki lantai dua yang berfungsi sebagai gudang dan ruang layanan, dan etalase yang lebih mudah dilihat dari jalan.',
        ],
        [
            'title' => 'Interior Kantor Notaris', 'slug' => 'interior-kantor-notaris', 'category' => 'Interior',
            'summary' => 'Penataan ruang kerja dan ruang tamu kantor profesional yang tenang, rapi, dan mudah dirawat.',
            'client' => '', 'show_client' => false, 'location' => 'Bandung, Jawa Barat', 'year' => 2025,
            'image' => 'prj3', 'gallery' => [], 'featured' => false, 'days' => 30,
            'content' => $p('Kantor ini membutuhkan ruang yang memberi kesan teratur bagi klien, sekaligus penyimpanan berkas yang memadai.'),
            'challenge' => 'Ruang sempit dengan banyak berkas fisik yang harus tetap mudah diakses dan aman.',
            'solution' => 'Kami merancang lemari arsip custom setinggi plafon, meja kerja dengan laci terintegrasi, dan pencahayaan yang merata di ruang tamu.',
            'results' => 'Berkas tertata di satu dinding, dan ruang tamu terasa lebih lega untuk menerima klien.',
        ],
        [
            'title' => 'Pembangunan Gudang Distribusi Kecil', 'slug' => 'pembangunan-gudang-distribusi-kecil', 'category' => 'Pembangunan',
            'summary' => 'Gudang sederhana dengan rangka baja ringan dan area bongkar muat untuk usaha distribusi lokal.',
            'client' => 'CV Distribusi Contoh', 'show_client' => false, 'location' => 'Bandung Barat, Jawa Barat', 'year' => 2024,
            'image' => 'prj4', 'gallery' => [], 'featured' => false, 'days' => 45,
            'content' => $p('Pembangunan gudang dari lahan kosong, mulai dari pondasi sampai atap dan area bongkar muat.'),
            'challenge' => 'Lahan sedikit berkontur dan jadwal harus selesai sebelum musim hujan.',
            'solution' => 'Pematangan lahan dan pondasi dikerjakan lebih dulu, lalu rangka atap dipasang begitu pondasi siap supaya pekerjaan lanjutan terlindung dari hujan.',
            'results' => 'Gudang selesai dengan area bongkar muat yang dapat dilalui truk kecil.',
        ],
        [
            'title' => 'Renovasi Dapur dan Ruang Makan', 'slug' => 'renovasi-dapur-dan-ruang-makan', 'category' => 'Interior',
            'summary' => 'Dapur sempit ditata ulang dengan kabinet custom dan pencahayaan kerja yang lebih baik.',
            'client' => '', 'show_client' => false, 'location' => 'Lembang, Jawa Barat', 'year' => 2025,
            'image' => 'prj5', 'gallery' => [], 'featured' => false, 'days' => 60,
            'content' => $p('Dapur dengan tata letak yang tidak efisien diubah menjadi area kerja yang lebih nyaman.'),
            'challenge' => 'Ruang dapur kecil dengan peralatan yang berserakan dan sirkulasi yang sempit.',
            'solution' => 'Kabinet dibuat sesuai ukuran ruang dengan penyimpanan vertikal, dan lampu kerja dipasang di bawah kabinet atas.',
            'results' => 'Peralatan memiliki tempat tetap dan area kerja lebih longgar.',
        ],
        [
            'title' => 'Interior Kafe Tiga Lantai', 'slug' => 'interior-kafe-tiga-lantai', 'category' => 'Interior',
            'summary' => 'Penataan interior kafe dengan area bar, ruang duduk, dan lantai atas untuk pertemuan kecil.',
            'client' => 'Kafe Contoh', 'show_client' => false, 'location' => 'Bandung, Jawa Barat', 'year' => 2023,
            'image' => 'prj6', 'gallery' => [], 'featured' => false, 'days' => 90,
            'content' => $p('Kafe di bangunan tiga lantai ini membutuhkan interior yang hangat dan tahan pemakaian harian.'),
            'challenge' => 'Setiap lantai memiliki fungsi berbeda, dan material harus tahan lalu lintas pengunjung.',
            'solution' => 'Kami memakai material yang mudah dibersihkan di area bar, dan pencahayaan hangat di ruang duduk untuk membedakan suasana tiap lantai.',
            'results' => 'Tiap lantai memiliki suasana sendiri dengan perawatan harian yang mudah.',
        ],
    ],

    'article_categories' => ['Tips', 'Panduan'],

    'articles' => [
        [
            'title' => 'Cara membaca RAB renovasi agar tidak ada biaya tersembunyi', 'slug' => 'cara-membaca-rab-renovasi', 'category' => 'Panduan',
            'image' => 'art1', 'tags' => ['rab', 'renovasi', 'biaya'], 'days' => 3,
            'excerpt' => 'Rencana anggaran biaya yang baik merinci pekerjaan, volume, dan harga satuan. Ini yang perlu Anda periksa sebelum menyetujuinya.',
            'seo_description' => 'Panduan membaca RAB renovasi: item yang harus dirinci, cara memeriksa volume, dan pertanyaan yang perlu diajukan ke kontraktor.',
            'content' => $p('Rencana anggaran biaya (RAB) adalah dokumen terpenting sebelum renovasi dimulai. Dokumen ini menentukan apa yang dikerjakan dan berapa biayanya.')
                . $h('Periksa rincian pekerjaan')
                . $p('RAB yang baik memuat daftar pekerjaan, volume, satuan, harga satuan, dan jumlah. Hati-hati dengan baris bertuliskan angka gelondongan tanpa rincian.')
                . $h('Tanyakan hal yang tidak jelas')
                . $ul(['Apakah harga sudah termasuk material dan upah?', 'Pekerjaan apa yang belum termasuk?', 'Bagaimana pekerjaan tambahan dihitung?'])
                . $p('Kontraktor yang terbuka akan senang menjelaskan. Jawaban yang menghindar adalah tanda untuk berhati-hati.'),
        ],
        [
            'title' => 'Lima hal yang perlu disiapkan sebelum survei lokasi', 'slug' => 'persiapan-sebelum-survei-lokasi', 'category' => 'Tips',
            'image' => 'art2', 'tags' => ['survei', 'renovasi', 'persiapan'], 'days' => 8,
            'excerpt' => 'Survei lokasi lebih efektif kalau Anda datang dengan gambaran kebutuhan. Siapkan lima hal ini.',
            'seo_description' => '',
            'content' => $p('Survei lokasi adalah langkah awal yang menentukan akurasi rencana dan anggaran. Persiapan sederhana membuat survei jauh lebih berguna.')
                . $ul(['Daftar kebutuhan, urut dari yang paling penting.', 'Foto kondisi saat ini dan masalah yang dirasakan.', 'Perkiraan anggaran, walau berupa rentang.', 'Target waktu, misalnya sebelum acara tertentu.', 'Dokumen bangunan seperti denah atau sertifikat, kalau ada.'])
                . $p('Jangan khawatir kalau belum lengkap. Tim survei akan membantu melengkapinya di lokasi.'),
        ],
        [
            'title' => 'Kapan renovasi butuh persetujuan bangunan?', 'slug' => 'renovasi-dan-persetujuan-bangunan', 'category' => 'Panduan',
            'image' => 'art3', 'tags' => ['perizinan', 'renovasi'], 'days' => 14,
            'excerpt' => '',
            'seo_description' => 'Penjelasan umum kapan renovasi memerlukan persetujuan bangunan dan mengapa Anda perlu bertanya ke dinas setempat.',
            'content' => $p('Tidak semua renovasi butuh perizinan, tetapi perubahan tertentu bisa membutuhkannya. Ketentuannya ditetapkan pemerintah daerah, jadi bisa berbeda antar wilayah.')
                . $h('Perubahan yang perlu dicek')
                . $ul(['Penambahan lantai atau luas bangunan.', 'Perubahan fungsi bangunan, misalnya rumah menjadi tempat usaha.', 'Perubahan struktur utama.'])
                . $h('Langkah yang aman')
                . $p('Tanyakan ke dinas yang menangani perizinan bangunan di daerah Anda sebelum pekerjaan dimulai. Artikel ini bersifat umum dan bukan nasihat hukum.'),
        ],
        [
            'title' => 'Memilih material lantai untuk ruko dengan lalu lintas tinggi', 'slug' => 'material-lantai-untuk-ruko', 'category' => 'Tips',
            'image' => 'art4', 'tags' => ['material', 'ruko', 'lantai'], 'days' => 21,
            'excerpt' => 'Lantai ruko dipakai sepanjang hari. Pertimbangkan daya tahan, kemudahan bersih, dan keamanan saat basah.',
            'seo_description' => '',
            'content' => $p('Lantai di area usaha menerima beban yang jauh lebih berat daripada lantai rumah. Pilihan material memengaruhi biaya perawatan jangka panjang.')
                . $h('Hal yang dipertimbangkan')
                . $ul(['Ketahanan terhadap gesekan dan goresan.', 'Kemudahan dibersihkan.', 'Tingkat licin ketika basah.', 'Biaya pemasangan dan perbaikan.'])
                . $p('Minta contoh material dan lihat langsung sebelum memutuskan, karena warna dan tekstur di katalog bisa berbeda.'),
        ],
        [
            'title' => 'Menata dapur kecil agar tetap lega', 'slug' => 'menata-dapur-kecil', 'category' => 'Tips',
            'image' => 'art5', 'tags' => ['interior', 'dapur'], 'days' => 28,
            'excerpt' => 'Dapur kecil bisa tetap nyaman dengan penyimpanan vertikal dan tata letak yang mengikuti alur memasak.',
            'seo_description' => '',
            'content' => $p('Ruang kecil bukan berarti tidak nyaman. Kuncinya ada pada tata letak dan penyimpanan.')
                . $ul(['Susun peralatan mengikuti alur: simpan, cuci, siapkan, masak.', 'Manfaatkan dinding untuk penyimpanan vertikal.', 'Pilih kabinet dengan pintu geser atau pintu yang tidak memakan ruang.', 'Pasang lampu kerja di bawah kabinet atas.'])
                . $p('Ukur ruang dengan teliti sebelum memesan kabinet, karena selisih beberapa sentimeter bisa menentukan.'),
        ],
        [
            'title' => 'Tanda atap bocor yang sebaiknya tidak ditunda', 'slug' => 'tanda-atap-bocor', 'category' => 'Tips',
            'image' => 'art6', 'tags' => ['perawatan', 'atap'], 'days' => 40,
            'excerpt' => 'Noda di plafon dan cat mengelupas sering jadi tanda awal kebocoran. Tangani sebelum kerusakan meluas.',
            'seo_description' => '',
            'content' => $p('Kebocoran atap jarang langsung besar. Biasanya ada tanda awal yang bisa dikenali.')
                . $ul(['Noda kecokelatan di plafon.', 'Cat dinding menggelembung atau mengelupas.', 'Bau lembap di ruangan atas.', 'Talang tersumbat atau berkarat.'])
                . $p('Perbaikan di tahap awal biasanya jauh lebih murah daripada mengganti plafon dan rangka yang sudah rusak.'),
        ],
    ],

    'testimonials' => [
        ['name' => 'Rina Kusuma', 'job_title' => 'Pemilik rumah', 'company' => '', 'quote' => 'Dari awal semua dijelaskan tertulis, termasuk biaya dan jadwal. Saat ada tambahan pekerjaan, kami diajak bicara dulu sebelum dikerjakan.'],
        ['name' => 'Agus Prasetyo', 'job_title' => 'Pemilik usaha', 'company' => 'Toko Sinar Pagi', 'quote' => 'Toko kami tetap bisa buka selama renovasi. Tim mengatur jam kerja yang berisik di luar jam ramai, dan laporan progresnya rutin.'],
        ['name' => 'Dewi Lestari', 'job_title' => 'Notaris', 'company' => '', 'quote' => 'Lemari arsip custom-nya pas dengan ruangan. Komunikasinya enak, dan hasil akhir sesuai gambar yang kami setujui di awal.'],
        ['name' => 'Budi Santoso', 'job_title' => 'Direktur', 'company' => 'CV Distribusi Contoh', 'quote' => 'Pembangunan gudang berjalan sesuai tahapan yang disepakati. Kami selalu tahu posisi pekerjaan dari laporan mingguan.'],
    ],

    'faqs' => [
        ['question' => 'Berapa biaya renovasi?', 'category' => 'Biaya', 'answer' => 'Biaya tergantung lingkup, material, dan kondisi bangunan. Setelah survei kami berikan RAB tertulis yang dirinci per pekerjaan, jadi Anda bisa menyesuaikan sebelum menyetujui.'],
        ['question' => 'Apakah ada biaya untuk survei lokasi?', 'category' => 'Biaya', 'answer' => 'Survei awal di wilayah Bandung Raya tidak dipungut biaya. Jika lokasi di luar wilayah itu, kami informasikan lebih dulu bila ada biaya transportasi.'],
        ['question' => 'Bagaimana sistem pembayarannya?', 'category' => 'Biaya', 'answer' => 'Pembayaran dibagi per tahapan pekerjaan yang disepakati di kontrak, bukan sekaligus di awal.'],
        ['question' => 'Berapa lama pengerjaan?', 'category' => 'Proses kerja', 'answer' => 'Setelah survei, kami berikan jadwal tertulis per tahapan. Lamanya bergantung pada lingkup dan kondisi bangunan.'],
        ['question' => 'Bisakah rumah tetap ditempati selama renovasi?', 'category' => 'Proses kerja', 'answer' => 'Bisa untuk banyak kasus. Pekerjaan dibagi per zona supaya sebagian ruang tetap bisa dipakai. Hal ini kami bahas saat survei.'],
        ['question' => 'Bagaimana kalau hasil pekerjaan tidak sesuai?', 'category' => '', 'answer' => 'Kami memeriksa hasil bersama Anda saat serah terima. Kekurangan yang ditemukan dicatat dan diperbaiki sebelum pekerjaan dinyatakan selesai.'],
    ],

    'inquiries' => [
        ['name' => 'Hendra Wijaya', 'email' => 'hendra@contoh.example', 'whatsapp' => '6281200001111', 'company' => 'CV Wijaya Jaya', 'need_type' => 'Permintaan penawaran', 'message' => 'Kami ingin merenovasi kantor dua lantai di Cimahi. Mohon info jadwal survei lokasi dan perkiraan prosesnya.', 'ip' => '203.0.113.10', 'status' => 'new', 'note' => ''],
        ['name' => 'Sari Anggraini', 'email' => 'sari@contoh.example', 'whatsapp' => '6281200002222', 'company' => '', 'need_type' => 'Desain dan Interior', 'message' => 'Saya ingin menata ulang dapur rumah, ukurannya kecil. Apakah bisa konsultasi dulu sebelum memutuskan?', 'ip' => '203.0.113.11', 'status' => 'contacted', 'note' => 'Sudah dihubungi lewat WhatsApp. Survei dijadwalkan pekan depan.'],
        ['name' => 'Tono Setiawan', 'email' => 'tono@contoh.example', 'whatsapp' => '6281200003333', 'company' => 'Toko Maju', 'need_type' => 'Konsultasi umum', 'message' => 'Tanya soal perbaikan atap ruko yang bocor di beberapa titik. Bisa diperiksa minggu ini?', 'ip' => '203.0.113.12', 'status' => 'closed', 'note' => 'Pekerjaan selesai, klien puas.'],
    ],
];