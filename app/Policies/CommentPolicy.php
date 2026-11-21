<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CommentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): Response
    {
        return $user->role?->name === Role::READER
            ? Response::allow()
            : Response::deny('Недостаточно прав для просмотра комментариев.');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Comment $comment): Response
    {
        return $user->role?->name === Role::READER
            ? Response::allow()
            : Response::deny('Недостаточно прав для просмотра этого комментария.');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        return $user->role?->name === Role::READER
            ? Response::allow()
            : Response::deny('Добавлять комментарии могут только читатели и модераторы.');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Comment $comment): Response
    {
        return Response::deny('Изменять комментарии может только модератор.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Comment $comment): Response
    {
        return Response::deny('Удалять комментарии может только модератор.');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Comment $comment): Response
    {
        return Response::deny('Восстанавливать комментарии может только модератор.');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Comment $comment): Response
    {
        return Response::deny('Удалять комментарии без возможности восстановления может только модератор.');
    }
}
