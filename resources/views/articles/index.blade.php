@extends('layouts.app')

@section('title', 'Новости')

@section('content')
    <section class="page-section">
        <h1>Новости</h1>
        <p class="section-description">Последние новости и публикации.</p>
        @can('manage-articles')
            <p><a class="article-action-primary" href="{{ route('articles.create') }}">Добавить новость</a></p>
        @endcan

        @if (session('status'))
            <p class="form-success" role="status">{{ session('status') }}</p>
        @endif

        <div class="articles-grid">
            @forelse ($articles as $article)
                <article class="article-card">
                    <div class="article-content">
                        @if ($article->published_at)
                            <time class="article-date" datetime="{{ $article->published_at->toDateString() }}">
                                {{ $article->published_at->format('d.m.Y') }}
                            </time>
                        @endif
                        <h2><a href="{{ route('articles.show', $article) }}">{{ $article->title }}</a></h2>
                        <p>{{ \Illuminate\Support\Str::limit($article->content, 180) }}</p>
                        @can('manage-articles')
                            <div class="article-actions">
                                <a class="article-action-link" href="{{ route('articles.edit', $article) }}">Изменить</a>
                                <form method="POST" action="{{ route('articles.destroy', $article) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="article-delete-button" type="submit">Удалить</button>
                                </form>
                            </div>
                        @endcan
                    </div>
                </article>
            @empty
                <p>Новостей пока нет.</p>
            @endforelse
        </div>

        @if ($articles->hasPages())
            <nav class="articles-pagination" aria-label="Навигация по новостям">
                <div class="pagination-controls">
                    @if ($articles->onFirstPage())
                        <span class="pagination-button is-disabled" aria-disabled="true">
                            <span aria-hidden="true">&lsaquo;</span>
                            Назад
                        </span>
                    @else
                        <a class="pagination-button" href="{{ $articles->previousPageUrl() }}" rel="prev">
                            <span aria-hidden="true">&lsaquo;</span>
                            Назад
                        </a>
                    @endif

                    <span class="pagination-current">
                        Страница <strong>{{ $articles->currentPage() }}</strong>
                        из {{ $articles->lastPage() }}
                    </span>

                    @if ($articles->hasMorePages())
                        <a class="pagination-button" href="{{ $articles->nextPageUrl() }}" rel="next">
                            Вперёд
                            <span aria-hidden="true">&rsaquo;</span>
                        </a>
                    @else
                        <span class="pagination-button is-disabled" aria-disabled="true">
                            Вперёд
                            <span aria-hidden="true">&rsaquo;</span>
                        </span>
                    @endif
                </div>

                <p class="pagination-summary">
                    Показаны записи {{ $articles->firstItem() }}–{{ $articles->lastItem() }}
                    из {{ $articles->total() }}
                </p>
            </nav>
        @endif
    </section>
@endsection
