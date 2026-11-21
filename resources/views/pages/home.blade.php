
@extends('layouts.app')

@section('title', 'Главная страница')

@section('content')

    <section class="page-section">
        <h1>Публикации</h1>
        <p class="section-description">Выберите изображение или заголовок, чтобы открыть публикацию в галерее.</p>

        <div class="articles-grid">
            @foreach ($articles as $index => $article)
                <article class="article-card">
                    <a class="article-image-link" href="{{ route('galery', ['article' => $index]) }}">
                        <img
                            class="article-preview"
                            src="{{ asset($article['preview_image']) }}"
                            alt="{{ $article['name'] }}"
                        >
                    </a>

                    <div class="article-content">
                        <time class="article-date">{{ $article['date'] }}</time>
                        <h2>
                            <a href="{{ route('galery', ['article' => $index]) }}">
                                {{ $article['name'] }}
                            </a>
                        </h2>
                        <p>{{ $article['shortDesc'] ?? \Illuminate\Support\Str::limit($article['desc'], 160) }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

@endsection
