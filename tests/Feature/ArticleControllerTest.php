<?php

namespace Tests\Feature;

use App\Mail\ArticleCreated;
use App\Models\Article;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ArticleControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_news_page_displays_articles_from_the_database(): void
    {
        $this->actingAs(User::factory()->create());

        Article::factory()->create([
            'title' => 'Важная новость',
            'content' => 'Текст новости для списка.',
            'published_at' => now(),
        ]);

        $this->get(route('articles.index'))
            ->assertOk()
            ->assertViewIs('articles.index')
            ->assertSee('Важная новость')
            ->assertSee('Текст новости для списка.');
    }

    public function test_news_page_paginates_articles(): void
    {
        $this->actingAs(User::factory()->create());

        foreach (range(1, 11) as $day) {
            Article::factory()->create([
                'title' => "Новость {$day}",
                'published_at' => now()->subDays($day),
            ]);
        }

        $this->get(route('articles.index', ['page' => 2]))
            ->assertOk()
            ->assertSee('Новость 11')
            ->assertDontSee('Новость 10')
            ->assertSee('Страница')
            ->assertSee('из 2')
            ->assertSee('Показаны записи 11–11')
            ->assertSee('из 11')
            ->assertDontSee('Showing 11 to 20 of 20 results');
    }

    public function test_database_seeder_creates_fake_news_articles(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('articles', 20);
    }

    public function test_create_and_edit_forms_include_csrf_protection_and_article_values(): void
    {
        $this->actingAs(User::factory()->moderator()->create());

        $article = Article::factory()->create([
            'title' => 'Заголовок для редактирования',
            'content' => 'Исходный текст новости.',
        ]);

        $this->get(route('articles.create'))
            ->assertOk()
            ->assertViewIs('articles.create')
            ->assertSee('action="'.route('articles.store').'"', false)
            ->assertSee('name="_token"', false)
            ->assertSee('name="title"', false)
            ->assertSee('name="content"', false);

        $this->get(route('articles.edit', $article))
            ->assertOk()
            ->assertViewIs('articles.edit')
            ->assertSee('action="'.route('articles.update', $article).'"', false)
            ->assertSee('name="_method" value="PUT"', false)
            ->assertSee('Заголовок для редактирования')
            ->assertSee('Исходный текст новости.');
    }

    public function test_valid_article_submission_creates_article(): void
    {
        $moderator = User::factory()->moderator()->create();
        $this->actingAs($moderator);
        Mail::fake();

        $response = $this->post(route('articles.store'), [
            'title' => 'Новая статья',
            'content' => 'Содержимое новой статьи.',
            'published_at' => '2026-11-07T10:30',
        ]);

        $response
            ->assertRedirect(route('articles.index'))
            ->assertSessionHas('status', 'Новость успешно создана.');

        $this->assertDatabaseHas('articles', [
            'title' => 'Новая статья',
            'content' => 'Содержимое новой статьи.',
        ]);

        Mail::assertSent(ArticleCreated::class, function (ArticleCreated $mail) use ($moderator): bool {
            $mail->assertHasSubject('Добавлена новая статья');
            $mail->assertSeeInHtml('Новая статья');
            $mail->assertSeeInHtml('Содержимое новой статьи.');

            return $mail->hasTo($moderator->email) && $mail->article->exists;
        });
    }

    public function test_invalid_article_submission_redirects_with_validation_errors(): void
    {
        $this->actingAs(User::factory()->moderator()->create());
        Mail::fake();

        $response = $this->from(route('articles.create'))->post(route('articles.store'), [
            'title' => str_repeat('а', 256),
            'content' => '',
            'published_at' => 'не дата',
        ]);

        $response
            ->assertRedirect(route('articles.create'))
            ->assertSessionHasErrors([
                'title' => 'Заголовок новости не должен быть длиннее 255 символов.',
                'content' => 'Укажите текст новости.',
                'published_at' => 'Укажите корректную дату публикации.',
            ]);

        $this->assertDatabaseCount('articles', 0);
        Mail::assertNotSent(ArticleCreated::class);
    }

    public function test_valid_article_submission_updates_existing_article(): void
    {
        $this->actingAs(User::factory()->moderator()->create());

        $article = Article::factory()->create([
            'title' => 'Старый заголовок',
            'content' => 'Старый текст.',
        ]);

        $response = $this->put(route('articles.update', $article), [
            'title' => 'Обновлённый заголовок',
            'content' => 'Обновлённый текст.',
            'published_at' => null,
        ]);

        $response
            ->assertRedirect(route('articles.index'))
            ->assertSessionHas('status', 'Новость успешно обновлена.');

        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'title' => 'Обновлённый заголовок',
            'content' => 'Обновлённый текст.',
            'published_at' => null,
        ]);
    }

    public function test_invalid_article_update_keeps_existing_article_unchanged(): void
    {
        $this->actingAs(User::factory()->moderator()->create());

        $article = Article::factory()->create([
            'title' => 'Сохранённый заголовок',
            'content' => 'Сохранённый текст.',
        ]);

        $response = $this->from(route('articles.edit', $article))
            ->put(route('articles.update', $article), [
                'title' => '',
                'content' => '',
                'published_at' => null,
            ]);

        $response
            ->assertRedirect(route('articles.edit', $article))
            ->assertSessionHasErrors([
                'title' => 'Укажите заголовок новости.',
                'content' => 'Укажите текст новости.',
            ]);

        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'title' => 'Сохранённый заголовок',
            'content' => 'Сохранённый текст.',
        ]);
    }

    public function test_article_can_be_read_and_deleted(): void
    {
        $this->actingAs(User::factory()->moderator()->create());

        $article = Article::factory()->create([
            'title' => 'Статья для просмотра',
            'content' => 'Полный текст статьи.',
        ]);

        $this->get(route('articles.show', $article))
            ->assertOk()
            ->assertViewIs('articles.show')
            ->assertSee('Статья для просмотра')
            ->assertSee('Полный текст статьи.');

        $this->delete(route('articles.destroy', $article))
            ->assertRedirect(route('articles.index'))
            ->assertSessionHas('status', 'Новость успешно удалена.');

        $this->assertDatabaseMissing('articles', [
            'id' => $article->id,
        ]);
    }
}
