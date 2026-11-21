<form class="registration-form" method="POST"
      action="{{ $article->exists ? route('articles.update', $article) : route('articles.store') }}">
    @csrf
    @if ($article->exists)
        @method('PUT')
    @endif

    <div class="form-field">
        <label for="title">Заголовок</label>
        <input id="title" name="title" type="text" value="{{ old('title', $article->title) }}" required maxlength="255">
        @error('title')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="form-field">
        <label for="content">Текст новости</label>
        <textarea id="content" name="content" rows="8" required>{{ old('content', $article->content) }}</textarea>
        @error('content')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="form-field">
        <label for="published_at">Дата публикации</label>
        <input id="published_at" name="published_at" type="datetime-local"
               value="{{ old('published_at', $article->published_at?->format('Y-m-d\TH:i')) }}">
        @error('published_at')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <button class="registration-submit" type="submit">
        {{ $article->exists ? 'Сохранить изменения' : 'Создать новость' }}
    </button>
    <a class="article-action-link" href="{{ route('articles.index') }}">Отмена</a>
</form>
