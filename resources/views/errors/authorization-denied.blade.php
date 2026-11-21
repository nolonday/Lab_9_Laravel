@extends('layouts.app')

@section('title', 'Доступ запрещён')

@section('content')
    <section class="page-section">
        <h1>Доступ запрещён</h1>
        <p role="alert">{{ $message }}</p>
        <p><a class="article-action-link" href="{{ route('articles.index') }}">Вернуться к новостям</a></p>
    </section>
@endsection
