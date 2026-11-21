<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ArticlePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): Response
    {
        return $user->role?->name === Role::READER
            ? Response::allow()
            : Response::deny('Недостаточно прав для просмотра новостей.');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Article $article): Response
    {
        return $user->role?->name === Role::READER
            ? Response::allow()
            : Response::deny('Недостаточно прав для просмотра этой новости.');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        return Response::deny('Создавать новости может только модератор.');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Article $article): Response
    {
        return Response::deny('Изменять новости может только модератор.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Article $article): Response
    {
        return Response::deny('Удалять новости может только модератор.');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Article $article): Response
    {
        return Response::deny('Восстанавливать новости может только модератор.');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Article $article): Response
    {
        return Response::deny('Удалять новости без возможности восстановления может только модератор.');
    }
}
