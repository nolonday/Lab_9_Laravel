@extends('layouts.app')

@section('title', 'Редактировать новость')

@section('content')
    <section class="page-section registration-page">
        <div class="registration-heading">
            <h1>Редактировать новость</h1>
            <p>Измените данные публикации и сохраните их.</p>
        </div>

        <div class="content-card registration-card">
            @include('articles.form')
        </div>
    </section>
@endsection
