@extends('layouts.app')

@section('title', 'Вход')

@section('content')
    <section class="page-section registration-page">
        <div class="registration-heading">
            <span class="registration-eyebrow">Рады вас видеть</span>
            <h1>Вход в аккаунт</h1>
            <p>Введите email и пароль для авторизации.</p>
        </div>

        <div class="content-card registration-card">
            @if (session('status'))
                <p class="form-success" role="status">{{ session('status') }}</p>
            @endif

            <form class="registration-form" method="POST" action="{{ route('login.store') }}">
                @csrf

                <div class="form-field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email">
                    @error('email')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-field">
                    <label for="password">Пароль</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password">
                    @error('password')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <label class="remember-field">
                    <input name="remember" type="checkbox" value="1">
                    Запомнить меня
                </label>

                <button class="registration-submit" type="submit">Войти</button>
            </form>

            <p class="auth-switch">Нет аккаунта? <a href="{{ route('register') }}">Зарегистрироваться</a></p>
        </div>
    </section>
@endsection
