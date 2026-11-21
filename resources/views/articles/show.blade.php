@extends('layouts.app')

@section('title', $article->title)

@section('content')
    <section class="page-section article-detail">
        <article class="content-card">
            @if ($article->published_at)
                <time class="article-date" datetime="{{ $article->published_at->toDateString() }}">
                    {{ $article->published_at->format('d.m.Y') }}
                </time>
            @endif
            <h1>{{ $article->title }}</h1>
            <p class="article-detail-content">{{ $article->content }}</p>

            <div class="article-actions">
                @can('manage-articles')
                    <a class="article-action-link" href="{{ route('articles.edit', $article) }}">Изменить</a>
                    <form method="POST" action="{{ route('articles.destroy', $article) }}">
                        @csrf
                        @method('DELETE')
                        <button class="article-delete-button" type="submit">Удалить</button>
                    </form>
                @endcan
                <a class="article-action-link" href="{{ route('articles.index') }}">К списку новостей</a>
            </div>
        </article>

        <section class="content-card">
            <h2>Комментарии</h2>

            @if (session('status'))
                <p class="form-success" role="status">{{ session('status') }}</p>
            @endif

            @forelse ($comments as $comment)
                <article class="article-comment">
                    <p><strong>{{ $comment->user->name }}</strong></p>
                    <p>{{ $comment->content }}</p>

                    @can('update', $comment)
                        <form class="registration-form" method="POST"
                              action="{{ route('articles.comments.update', [$article, $comment]) }}">
                            @csrf
                            @method('PUT')
                            <label for="comment-{{ $comment->id }}">Изменить комментарий</label>
                            <textarea id="comment-{{ $comment->id }}" name="content" rows="3" required>{{ old('content', $comment->content) }}</textarea>
                            @error('content')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                            <button class="registration-submit" type="submit">Сохранить</button>
                        </form>
                    @endcan

                    @can('delete', $comment)
                        <form method="POST"
                              action="{{ route('articles.comments.destroy', [$article, $comment]) }}">
                            @csrf
                            @method('DELETE')
                            <button class="article-delete-button" type="submit">Удалить комментарий</button>
                        </form>
                    @endcan
                </article>
            @empty
                <p>Комментариев пока нет.</p>
            @endforelse

            @can('create', \App\Models\Comment::class)
                <form class="registration-form" method="POST"
                      action="{{ route('articles.comments.store', $article) }}">
                    @csrf
                    <div class="form-field">
                        <label for="comment-content">Добавить комментарий</label>
                        <textarea id="comment-content" name="content" rows="4" maxlength="5000" required>{{ old('content') }}</textarea>
                        @error('content')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <button class="registration-submit" type="submit">Отправить</button>
                </form>
            @endcan
        </section>
    </section>
@endsection
