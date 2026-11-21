@extends('layouts.app')

@section('title', $article['name'])

@section('content')
    <section class="page-section">
        <article class="gallery-article">
            <a class="button gallery-back" href="{{ route('home') }}">Все публикации</a>
            <time class="article-date">{{ $article['date'] }}</time>
            <h1>{{ $article['name'] }}</h1>

            <img
                class="gallery-image"
                src="{{ asset($article['full_image']) }}"
                alt="{{ $article['name'] }}"
            >

            <div class="gallery-description">
                <p>{{ $article['desc'] }}</p>
            </div>
        </article>
    </section>
@endsection
