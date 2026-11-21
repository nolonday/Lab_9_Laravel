
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Мой сайт')</title>

    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>

    <header class="header">
        <div class="container header-content">

            <div class="logo">
                <a href="{{ route('home') }}">МОЙ САЙТ</a>
            </div>

            <nav class="navigation">
                <a href="{{ route('home') }}"
                   class="{{ request()->routeIs('home') ? 'active' : '' }}">
                    Главная
                </a>

                <a href="{{ route('articles.index') }}"
                   class="{{ request()->routeIs('articles.*') ? 'active' : '' }}">
                    Новости
                </a>

                @can('manage-articles')
                    <a href="{{ route('articles.create') }}"
                       class="{{ request()->routeIs('articles.create') ? 'active' : '' }}">
                        Создать новость
                    </a>
                @endcan

                @can('moderate-comments')
                    <a href="{{ route('moderation.comments.index') }}"
                       class="{{ request()->routeIs('moderation.comments.*') ? 'active' : '' }}">
                        Модерация комментариев
                    </a>
                @endcan

                <a href="{{ route('about') }}"
                   class="{{ request()->routeIs('about') ? 'active' : '' }}">
                    О нас
                </a>

                <a href="{{ route('contacts') }}"
                   class="{{ request()->routeIs('contacts') ? 'active' : '' }}">
                    Контакты
                </a>

                @guest
                    <a href="{{ route('register') }}"
                       class="{{ request()->routeIs('register*') ? 'active' : '' }}">
                        Регистрация
                    </a>
                    <a href="{{ route('login') }}"
                       class="{{ request()->routeIs('login*') ? 'active' : '' }}">
                        Войти
                    </a>
                @else
                    <form class="navigation-logout" method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit">Выйти</button>
                    </form>
                @endguest
            </nav>

        </div>
    </header>

    <main class="container main-content">
        @yield('content')
    </main>

    <footer class="footer">
        <div class="container">
            <p>Соловьев Даниил Дмитриевич | Группа 257-321</p>
            <p>© {{ date('Y') }} Учебный сайт</p>
        </div>
    </footer>

</body>
</html>
