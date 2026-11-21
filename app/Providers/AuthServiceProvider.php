<?php

namespace App\Providers;

use App\Models\Article;
use App\Models\Comment;
use App\Models\User;
use App\Policies\ArticlePolicy;
use App\Policies\CommentPolicy;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Gate::policy(Article::class, ArticlePolicy::class);
        Gate::policy(Comment::class, CommentPolicy::class);

        Gate::before(static function (User $user, string $ability): ?bool {
            return $user->isModerator() ? true : null;
        });

        Gate::define('manage-articles', static function (User $user): Response {
            return Response::deny('Управлять новостями может только модератор.');
        });

        Gate::define('moderate-comments', static function (User $user): Response {
            return Response::deny('Модерировать комментарии может только модератор.');
        });
    }
}
