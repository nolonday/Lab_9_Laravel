@extends('layouts.app')

@section('title', 'Регистрация')

@section('content')

    <section class="page-section registration-page">
        <div class="registration-heading">
            <span class="registration-eyebrow">Создайте аккаунт</span>
            <h1>Регистрация</h1>
            <p>Заполните данные, чтобы создать аккаунт.</p>
        </div>

        <div class="content-card registration-card">
            <form class="registration-form" method="POST" action="{{ route('register.store') }}">
                @csrf

                <div class="form-field">
                    <label for="name">Имя</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required maxlength="255">
                    @error('name')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required maxlength="255">
                    @error('email')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-field">
                    <label for="password">Пароль</label>
                    <input id="password" name="password" type="password" required minlength="8" autocomplete="new-password">
                    <span class="form-hint">Не менее 8 символов</span>
                    @error('password')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <button class="registration-submit" type="submit">Зарегистрироваться</button>
            </form>

            <p class="auth-switch">Уже есть аккаунт? <a href="{{ route('login') }}">Войти</a></p>
        </div>
    </section>

@endsection
