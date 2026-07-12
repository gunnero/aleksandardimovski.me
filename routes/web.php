<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');
Route::view('/about', 'pages.about')->name('about');
Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
Route::view('/experience', 'pages.experience')->name('experience');
Route::view('/resume', 'pages.resume')->name('resume');
Route::get('/resume/download', fn () => response()->download(public_path('files/aleksandar-dimovski-resume.pdf')))->name('resume.download');
Route::get('/articles', [ArticleController::class, 'index'])->name('articles.index');
Route::get('/articles/{article}', [ArticleController::class, 'show'])->name('articles.show');
Route::view('/contact', 'pages.contact')->name('contact');
Route::view('/privacy', 'pages.privacy')->name('privacy');
Route::view('/robots.txt', 'seo.robots')->name('robots');
Route::get('/sitemap.xml', [PageController::class, 'sitemap'])->name('sitemap');
