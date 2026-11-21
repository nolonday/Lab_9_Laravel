<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\MainController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'create'])->name('register');
    Route::post('/register', [AuthController::class, 'store'])->name('register.store');
    Route::get('/signin', [AuthController::class, 'create'])->name('signin');
    Route::post('/signin', [AuthController::class, 'store'])->name('signin.registration');

    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:6,1')
        ->name('login.store');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::resource('articles', ArticleController::class);
    Route::post('/articles/{article}/comments', [CommentController::class, 'store'])
        ->name('articles.comments.store');
    Route::put('/articles/{article}/comments/{comment}', [CommentController::class, 'update'])
        ->scopeBindings()
        ->name('articles.comments.update');
    Route::delete('/articles/{article}/comments/{comment}', [CommentController::class, 'destroy'])
        ->scopeBindings()
        ->name('articles.comments.destroy');

    Route::middleware('can:moderate-comments')
        ->prefix('moderation')
        ->name('moderation.')
        ->group(function () {
            Route::get('/comments', [CommentController::class, 'index'])
                ->name('comments.index');
            Route::post('/comments/{comment}/approve', [CommentController::class, 'approve'])
                ->name('comments.approve');
            Route::delete('/comments/{comment}', [CommentController::class, 'reject'])
                ->name('comments.reject');
        });

    Route::get('/galery/{article}', [MainController::class, 'galery'])
        ->whereNumber('article')
        ->name('galery');
});

Route::get('/', [MainController::class, 'index'])->name('home');

Route::get('/about', function () {
    return view('pages.about');
})->name('about');

Route::get('/contacts', function () {

    $contacts = [
        [
            'name' => 'Соловьев Даниил Дмитриевич',
            'position' => 'Студент, группа 257-321',
            'email' => 'student@example.com',
            'phone' => '+7 (900) 000-00-00',
        ],
        [
            'name' => 'Учебная часть',
            'position' => 'По учебным вопросам',
            'email' => 'study@example.com',
            'phone' => '+7 (900) 111-11-11',
        ],
    ];

    return view('pages.contacts', [
        'contacts' => $contacts,
    ]);

})->name('contacts');
