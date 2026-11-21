@extends('layouts.app')

@section('title', 'Модерация комментариев')

@section('content')
    <section class="page-section">
        <h1>Комментарии на модерации</h1>
        <p class="section-description">Проверьте новые комментарии и примите решение о публикации.</p>

        @if (session('status'))
            <p class="form-success" role="status">{{ session('status') }}</p>
        @endif

        @forelse ($comments as $comment)
            <article class="content-card article-comment">
                <p>
                    <strong>{{ $comment->user->name }}</strong>
                    к новости
                    <a href="{{ route('articles.show', $comment->article) }}">
                        {{ $comment->article->title }}
                    </a>
                </p>
                <p>{{ $comment->content }}</p>
                <div class="article-actions">
                    <form method="POST" action="{{ route('moderation.comments.approve', $comment) }}">
                        @csrf
                        <button class="registration-submit" type="submit">Одобрить</button>
                    </form>
                    <form method="POST" action="{{ route('moderation.comments.reject', $comment) }}">
                        @csrf
                        @method('DELETE')
                        <button class="article-delete-button" type="submit">Отклонить</button>
                    </form>
                </div>
            </article>
        @empty
            <p>Нет комментариев, ожидающих модерации.</p>
        @endforelse

        {{ $comments->links() }}
    </section>
@endsection
