<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\Workspace\ApplicationController;
use App\Http\Controllers\Workspace\CandidateProfileController;
use App\Http\Controllers\Workspace\JobInboxController;
use App\Http\Controllers\Workspace\PrivateDocumentController;
use App\Models\JobApplication;
use App\Models\JobOpportunity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');
Route::view('/about', 'pages.about')->name('about');
Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
Route::get('/projects/{project}/architecture.mmd', [ProjectController::class, 'diagram'])->name('projects.diagram');
Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
Route::view('/engineering-principles', 'pages.engineering-principles')->name('engineering-principles');
Route::view('/release-history', 'pages.release-history')->name('release-history');
Route::view('/experience', 'pages.experience')->name('experience');
Route::view('/resume', 'pages.resume')->name('resume');
Route::get('/resume/download', fn () => response()->download(public_path('files/aleksandar-dimovski-resume.pdf')))->name('resume.download');
Route::get('/articles', [ArticleController::class, 'index'])->name('articles.index');
Route::get('/articles/{article}', [ArticleController::class, 'show'])->name('articles.show');
Route::view('/contact', 'pages.contact')->name('contact');
Route::view('/privacy', 'pages.privacy')->name('privacy');
Route::view('/robots.txt', 'seo.robots')->name('robots');
Route::get('/sitemap.xml', [PageController::class, 'sitemap'])->name('sitemap');

// Deliberately absent from public navigation, sitemap, feeds, metadata and APIs.
Route::middleware('guest')->group(function (): void {
    Route::view('/workspace/login', 'workspace.login')->name('workspace.login');
    Route::post('/workspace/login', function (Request $request) {
        $credentials = $request->validate(['email' => 'required|email', 'password' => 'required|string']);
        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Invalid credentials.'])->onlyInput('email');
        }
        $request->session()->regenerate();

        return redirect()->intended(route('workspace.jobs.index'));
    })->middleware('throttle:5,1')->name('workspace.login.store');
});
Route::prefix('workspace')->name('workspace.')->middleware(['auth', 'verified', 'workspace.owner'])->group(function (): void {
    Route::get('/', function (Request $request) {
        return view('workspace.dashboard.index', [
            'jobCount' => JobOpportunity::where('user_id', $request->user()->id)->count(),
            'applicationCount' => JobApplication::where('user_id', $request->user()->id)->count(),
            'recentApplications' => JobApplication::where('user_id', $request->user()->id)->with('opportunity')->latest()->limit(8)->get(),
        ]);
    })->name('dashboard');
    Route::post('/logout', function (Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    })->name('logout');
    Route::get('/profile', [CandidateProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [CandidateProfileController::class, 'update'])->name('profile.update');
    Route::get('/jobs', [JobInboxController::class, 'index'])->name('jobs.index');
    Route::get('/jobs/{job}', [JobInboxController::class, 'show'])->name('jobs.show');
    Route::patch('/jobs/{job}/review', [JobInboxController::class, 'review'])->name('jobs.review');
    Route::get('/applications/{application}', [ApplicationController::class, 'show'])->name('applications.show');
    Route::post('/applications/{application}/approve-submission', [ApplicationController::class, 'approve'])->name('applications.approve');
    Route::post('/applications/{application}/decision', [ApplicationController::class, 'decision'])->name('applications.decision');
    Route::get('/applications/{application}/documents/{document}', [PrivateDocumentController::class, 'show'])->where('document', '[0-9a-fA-F-]{36}\.(pdf|docx|txt)')->name('applications.documents.show');
});
