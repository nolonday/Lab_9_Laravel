<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Добавлена новая статья</title>
</head>
<body>
    <h1>Добавлена новая статья</h1>

    <p><strong>Заголовок:</strong> {{ $article->title }}</p>
    <p><strong>Дата публикации:</strong>
        {{ $article->published_at?->format('d.m.Y H:i') ?? 'Не указана' }}
    </p>

    <h2>Содержание статьи</h2>
    <p>{{ $article->content }}</p>
</body>
</html>
