<?php

declare(strict_types=1);

use App\Controllers\Admin\ArticleController;
use App\Controllers\Admin\ArticleTaxonomyController;
use App\Controllers\Admin\AuthController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\FaqController;
use App\Controllers\Admin\HomepageController;
use App\Controllers\Admin\InquiryController;
use App\Controllers\Admin\MediaController;
use App\Controllers\Admin\MigrationController;
use App\Controllers\Admin\PageController;
use App\Controllers\Admin\ProjectCategoryController;
use App\Controllers\Admin\ProjectController;
use App\Controllers\Admin\ServiceController;
use App\Controllers\Admin\SettingsController;
use App\Controllers\Admin\TestimonialController;
use App\Core\Route;

Route::prefix('admin')->middleware('web')->name('admin.')->group(function () {
    // Tamu
    Route::get('/login', [AuthController::class, 'showLogin'])->middleware('guest')->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('guest')->name('login.attempt');

    // Harus login
    Route::group(['middleware' => ['auth']], function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Inquiry
        Route::get('/inquiries', [InquiryController::class, 'index'])
            ->middleware('can:inquiries.view')->name('inquiries');
        Route::get('/inquiries/{id}', [InquiryController::class, 'show'])
            ->middleware('can:inquiries.view')->name('inquiries.show');
        Route::post('/inquiries/{id}/update', [InquiryController::class, 'update'])
            ->middleware('can:inquiries.update')->name('inquiries.update');
        Route::post('/inquiries/{id}/delete', [InquiryController::class, 'delete'])
            ->middleware('can:inquiries.delete')->name('inquiries.delete');

        // Halaman
        Route::get('/pages', [PageController::class, 'index'])
            ->middleware('can:content.create,content.edit')->name('pages');
        Route::get('/pages/create', [PageController::class, 'create'])
            ->middleware('can:content.create')->name('pages.create');
        Route::post('/pages', [PageController::class, 'store'])
            ->middleware('can:content.create')->name('pages.store');
        Route::get('/pages/{id}/edit', [PageController::class, 'edit'])
            ->middleware('can:content.edit')->name('pages.edit');
        Route::post('/pages/{id}/update', [PageController::class, 'update'])
            ->middleware('can:content.edit')->name('pages.update');
        Route::post('/pages/{id}/delete', [PageController::class, 'delete'])
            ->middleware('can:content.delete_any,content.delete_own')->name('pages.delete');

        // Beranda
        Route::get('/homepage', [HomepageController::class, 'index'])
            ->middleware('can:content.edit')->name('homepage');
        Route::post('/homepage', [HomepageController::class, 'update'])
            ->middleware('can:content.edit')->name('homepage.update');

        // Layanan
        Route::get('/services', [ServiceController::class, 'index'])
            ->middleware('can:content.create,content.edit')->name('services');
        Route::get('/services/create', [ServiceController::class, 'create'])
            ->middleware('can:content.create')->name('services.create');
        Route::post('/services', [ServiceController::class, 'store'])
            ->middleware('can:content.create')->name('services.store');
        Route::get('/services/{id}/edit', [ServiceController::class, 'edit'])
            ->middleware('can:content.edit')->name('services.edit');
        Route::post('/services/{id}/update', [ServiceController::class, 'update'])
            ->middleware('can:content.edit')->name('services.update');
        Route::post('/services/{id}/move', [ServiceController::class, 'move'])
            ->middleware('can:content.edit')->name('services.move');
        Route::post('/services/{id}/delete', [ServiceController::class, 'delete'])
            ->middleware('can:content.delete_any,content.delete_own')->name('services.delete');

        // Portofolio
        Route::get('/projects', [ProjectController::class, 'index'])
            ->middleware('can:content.create,content.edit')->name('projects');
        Route::get('/projects/create', [ProjectController::class, 'create'])
            ->middleware('can:content.create')->name('projects.create');
        Route::post('/projects', [ProjectController::class, 'store'])
            ->middleware('can:content.create')->name('projects.store');
        Route::get('/projects/{id}/edit', [ProjectController::class, 'edit'])
            ->middleware('can:content.edit')->name('projects.edit');
        Route::post('/projects/{id}/update', [ProjectController::class, 'update'])
            ->middleware('can:content.edit')->name('projects.update');
        Route::post('/projects/{id}/move', [ProjectController::class, 'move'])
            ->middleware('can:content.edit')->name('projects.move');
        Route::post('/projects/{id}/delete', [ProjectController::class, 'delete'])
            ->middleware('can:content.delete_any,content.delete_own')->name('projects.delete');

        // Kategori proyek
        Route::get('/project-categories', [ProjectCategoryController::class, 'index'])
            ->middleware('can:content.edit')->name('project-categories');
        Route::post('/project-categories', [ProjectCategoryController::class, 'store'])
            ->middleware('can:content.edit')->name('project-categories.store');
        Route::post('/project-categories/{id}/update', [ProjectCategoryController::class, 'update'])
            ->middleware('can:content.edit')->name('project-categories.update');
        Route::post('/project-categories/{id}/delete', [ProjectCategoryController::class, 'delete'])
            ->middleware('can:content.delete_any')->name('project-categories.delete');

        // Artikel
        Route::get('/articles', [ArticleController::class, 'index'])
            ->middleware('can:content.create,content.edit')->name('articles');
        Route::get('/articles/create', [ArticleController::class, 'create'])
            ->middleware('can:content.create')->name('articles.create');
        Route::post('/articles', [ArticleController::class, 'store'])
            ->middleware('can:content.create')->name('articles.store');
        Route::get('/articles/{id}/edit', [ArticleController::class, 'edit'])
            ->middleware('can:content.edit')->name('articles.edit');
        Route::post('/articles/{id}/update', [ArticleController::class, 'update'])
            ->middleware('can:content.edit')->name('articles.update');
        Route::post('/articles/{id}/delete', [ArticleController::class, 'delete'])
            ->middleware('can:content.delete_any,content.delete_own')->name('articles.delete');

        // Kategori dan tag artikel ({type} = categories atau tags)
        Route::get('/taxonomy/{type}', [ArticleTaxonomyController::class, 'index'])
            ->where('type', 'categories|tags')
            ->middleware('can:content.edit')->name('taxonomy');
        Route::post('/taxonomy/{type}', [ArticleTaxonomyController::class, 'store'])
            ->where('type', 'categories|tags')
            ->middleware('can:content.edit')->name('taxonomy.store');
        Route::post('/taxonomy/{type}/{id}/update', [ArticleTaxonomyController::class, 'update'])
            ->where('type', 'categories|tags')
            ->middleware('can:content.edit')->name('taxonomy.update');
        Route::post('/taxonomy/{type}/{id}/delete', [ArticleTaxonomyController::class, 'delete'])
            ->where('type', 'categories|tags')
            ->middleware('can:content.delete_any')->name('taxonomy.delete');

        // Testimoni
        Route::get('/testimonials', [TestimonialController::class, 'index'])
            ->middleware('can:content.create,content.edit')->name('testimonials');
        Route::get('/testimonials/create', [TestimonialController::class, 'create'])
            ->middleware('can:content.create')->name('testimonials.create');
        Route::post('/testimonials', [TestimonialController::class, 'store'])
            ->middleware('can:content.create')->name('testimonials.store');
        Route::get('/testimonials/{id}/edit', [TestimonialController::class, 'edit'])
            ->middleware('can:content.edit')->name('testimonials.edit');
        Route::post('/testimonials/{id}/update', [TestimonialController::class, 'update'])
            ->middleware('can:content.edit')->name('testimonials.update');
        Route::post('/testimonials/{id}/move', [TestimonialController::class, 'move'])
            ->middleware('can:content.edit')->name('testimonials.move');
        Route::post('/testimonials/{id}/delete', [TestimonialController::class, 'delete'])
            ->middleware('can:content.delete_any,content.delete_own')->name('testimonials.delete');

        // FAQ
        Route::get('/faqs', [FaqController::class, 'index'])
            ->middleware('can:content.create,content.edit')->name('faqs');
        Route::get('/faqs/create', [FaqController::class, 'create'])
            ->middleware('can:content.create')->name('faqs.create');
        Route::post('/faqs', [FaqController::class, 'store'])
            ->middleware('can:content.create')->name('faqs.store');
        Route::get('/faqs/{id}/edit', [FaqController::class, 'edit'])
            ->middleware('can:content.edit')->name('faqs.edit');
        Route::post('/faqs/{id}/update', [FaqController::class, 'update'])
            ->middleware('can:content.edit')->name('faqs.update');
        Route::post('/faqs/{id}/move', [FaqController::class, 'move'])
            ->middleware('can:content.edit')->name('faqs.move');
        Route::post('/faqs/{id}/delete', [FaqController::class, 'delete'])
            ->middleware('can:content.delete_any,content.delete_own')->name('faqs.delete');

        // Media library
        Route::get('/media', [MediaController::class, 'index'])
            ->middleware('can:media.view')->name('media');
        Route::get('/media/browse', [MediaController::class, 'browse'])
            ->middleware('can:media.view')->name('media.browse');
        Route::post('/media', [MediaController::class, 'upload'])
            ->middleware('can:media.upload')->name('media.upload');
        Route::post('/media/{id}/update', [MediaController::class, 'update'])
            ->middleware('can:media.upload')->name('media.update');
        Route::post('/media/{id}/delete', [MediaController::class, 'delete'])
            ->middleware('can:media.delete')->name('media.delete');

        // Pengaturan website
        Route::get('/settings', [SettingsController::class, 'index'])
            ->middleware('can:settings.manage')->name('settings');
        Route::post('/settings', [SettingsController::class, 'update'])
            ->middleware('can:settings.manage')->name('settings.update');

        // Khusus Super Admin
        Route::get('/system/migrations', [MigrationController::class, 'index'])
            ->middleware('can:system.migrations')->name('migrations');
        Route::post('/system/migrations/run', [MigrationController::class, 'run'])
            ->middleware('can:system.migrations')->name('migrations.run');
    });
});