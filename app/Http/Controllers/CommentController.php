<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest;
use App\Models\Article;
use App\Models\Comment;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
    public function index(): View
    {
        return view('comments.moderation', [
            'comments' => Comment::query()
                ->where('is_approved', false)
                ->with(['article', 'user'])
                ->latest()
                ->paginate(10),
        ]);
    }

    public function store(CommentRequest $request, Article $article): RedirectResponse
    {
        $comment = $article->comments()->make($request->validated());
        $comment->user()->associate($request->user());
        $comment->save();

        return redirect()
            ->route('articles.show', $article)
            ->with('status', 'Комментарий отправлен и ожидает модерации.');
    }

    public function approve(Comment $comment): RedirectResponse
    {
        abort_if($comment->is_approved, 404);

        $comment->is_approved = true;
        $comment->save();

        return redirect()
            ->route('moderation.comments.index')
            ->with('status', 'Комментарий одобрен и опубликован.');
    }

    public function reject(Comment $comment): RedirectResponse
    {
        abort_if($comment->is_approved, 404);

        $comment->delete();

        return redirect()
            ->route('moderation.comments.index')
            ->with('status', 'Комментарий отклонён и удалён.');
    }

    public function update(
        CommentRequest $request,
        Article $article,
        Comment $comment,
    ): RedirectResponse {
        $comment->update($request->validated());

        return redirect()
            ->route('articles.show', $article)
            ->with('status', 'Комментарий успешно обновлён.');
    }

    public function destroy(Article $article, Comment $comment): RedirectResponse
    {
        Gate::authorize('delete', $comment);

        $comment->delete();

        return redirect()
            ->route('articles.show', $article)
            ->with('status', 'Комментарий успешно удалён.');
    }
}
