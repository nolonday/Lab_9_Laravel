@extends('layouts.app')

@section('title', 'Добавить новость')

@section('content')
    <section class="page-section registration-page">
        <div class="registration-heading">
            <h1>Добавить новость</h1>
            <p>Заполните заголовок и текст публикации.</p>
        </div>

        <div class="content-card registration-card">
            @include('articles.form')
        </div>
    </section>
@endsection
