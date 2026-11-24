<?php

declare(strict_types=1);

use App\Controllers\Site\BlogController;
use App\Controllers\Site\ContactController;
use App\Controllers\Site\FaqController;
use App\Controllers\Site\HomeController;
use App\Controllers\Site\PageController;
use App\Controllers\Site\ProjectController;
use App\Controllers\Site\SeoController;
use App\Controllers\Site\ServiceController;
use App\Core\Database;
use App\Core\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

// Route admin didaftarkan sebelum route publik dinamis
require __DIR__ . '/admin.php';

// SEO
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');

// Layanan
Route::get('/layanan', [ServiceController::class, 'index'])->name('services');
Route::get('/layanan/{slug}', [ServiceController::class, 'show'])
    ->where('slug', '[a-z0-9]+(-[a-z0-9]+)*')->name('services.show');

// Portofolio (kategori didaftarkan sebelum {slug})
Route::get('/proyek', [ProjectController::class, 'index'])->name('projects');
Route::get('/proyek/kategori/{slug}', [ProjectController::class, 'category'])
    ->where('slug', '[a-z0-9]+(-[a-z0-9]+)*')->name('projects.category');
Route::get('/proyek/{slug}', [ProjectController::class, 'show'])
    ->where('slug', '[a-z0-9]+(-[a-z0-9]+)*')->name('projects.show');

// Blog (kategori dan tag didaftarkan sebelum {slug})
Route::get('/blog', [BlogController::class, 'index'])->name('blog');
Route::get('/blog/kategori/{slug}', [BlogController::class, 'category'])
    ->where('slug', '[a-z0-9]+(-[a-z0-9]+)*')->name('blog.category');
Route::get('/blog/tag/{slug}', [BlogController::class, 'tag'])
    ->where('slug', '[a-z0-9]+(-[a-z0-9]+)*')->name('blog.tag');
Route::get('/blog/{slug}', [BlogController::class, 'show'])
    ->where('slug', '[a-z0-9]+(-[a-z0-9]+)*')->name('blog.show');

// FAQ dan kontak (tanpa middleware 'web': halaman publik tidak memakai session)
Route::get('/faq', [FaqController::class, 'index'])->name('faq');
Route::get('/kontak', [ContactController::class, 'show'])->name('contact');
Route::post('/kontak', [ContactController::class, 'submit'])->name('contact.submit');
Route::get('/kontak/terkirim', [ContactController::class, 'thanks'])->name('contact.thanks');

// Route uji, hanya aktif saat APP_DEBUG=true dan bukan production.
// Hapus blok ini kalau fondasi sudah terverifikasi.
if (config('app.debug')) {
    Route::get('/_dev/halo/{nama}', function (string $nama) {
        return 'Halo, ' . e($nama);
    })->where('nama', '[a-zA-Z]+')->name('dev.halo');

    Route::get('/_dev/json', fn () => ['ok' => true, 'waktu' => date('c')]);

    Route::get('/_dev/error', function () {
        throw new RuntimeException('Tes error handler');
    });

    Route::get('/_dev/db', function () {
        $row = Database::connection()->query('SELECT VERSION() AS versi')->fetch();

        return ['versi_db' => $row['versi']];
    });
}

// Halaman custom dari CMS. HARUS paling bawah: semua route spesifik di atas didahulukan.
Route::get('/{slug}', [PageController::class, 'show'])
    ->where('slug', '[a-z0-9]+(-[a-z0-9]+)*')->name('pages.show');