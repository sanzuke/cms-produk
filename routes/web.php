<?php

use App\Http\Controllers\Site\HomeController;
use App\Http\Controllers\Site\OrderController;
use App\Http\Controllers\Site\PackageController;
use App\Http\Controllers\Site\PageController;
use App\Http\Controllers\Site\PortalController;
use App\Http\Controllers\Site\PostController;
use App\Http\Controllers\Site\ProjectController;
use App\Http\Controllers\Site\ServiceController;
use App\Http\Controllers\Site\SeoController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/layanan', [ServiceController::class, 'index'])->name('services.index');
Route::get('/layanan/{slug}', [ServiceController::class, 'show'])->name('services.show');

Route::get('/portofolio', [ProjectController::class, 'index'])->name('projects.index');
Route::get('/portofolio/{slug}', [ProjectController::class, 'show'])->name('projects.show');

Route::get('/paket', [PackageController::class, 'index'])->name('packages.index');

Route::get('/pesan', [OrderController::class, 'create'])->name('order.create');
Route::post('/pesan', [OrderController::class, 'store'])->middleware('throttle:5,1')->name('order.store');
Route::get('/pesan/terima-kasih', [OrderController::class, 'thanks'])->name('order.thanks');

Route::get('/artikel', [PostController::class, 'index'])->name('posts.index');
Route::get('/artikel/{slug}', [PostController::class, 'show'])->name('posts.show');

Route::get('/tentang', [PageController::class, 'about'])->name('about');
Route::get('/kontak', [PageController::class, 'contact'])->name('contact');
Route::get('/faq', [PageController::class, 'faq'])->name('faq');
Route::get('/legal/privasi', [PageController::class, 'privacy'])->name('legal.privacy');
Route::get('/legal/syarat', [PageController::class, 'terms'])->name('legal.terms');

Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');

// Portal klien (tanpa akun): akses lewat link ber-token; semua halaman noindex + no-store.
Route::prefix('klien/{token}')->middleware('throttle:60,1,portal')->group(function () {
    Route::get('/', [PortalController::class, 'show'])->name('portal.show');
    Route::post('/invoice/{invoice}/bukti', [PortalController::class, 'proof'])->whereNumber('invoice')->name('portal.proof');
    Route::post('/favorit', [PortalController::class, 'favorite'])->name('portal.favorite');
    Route::post('/komentar', [PortalController::class, 'comment'])->middleware('throttle:10,1,portal-comment')->name('portal.comment');
    Route::get('/berkas/{index}', [PortalController::class, 'file'])->whereNumber('index')->name('portal.file');
    Route::get('/zip', [PortalController::class, 'zip'])->name('portal.zip');
});
