<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Comment;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_reader_can_view_news_and_add_comments_but_cannot_manage_content(): void
    {
        $reader = User::factory()->create();
        $article = Article::factory()->create();

        $this->actingAs($reader)
            ->get(route('articles.index'))
            ->assertOk()
            ->assertDontSee(route('articles.create'), false);

        $this->get(route('articles.create'))
            ->assertForbidden()
            ->assertSee('Создавать новости может только модератор.');

        $this->getJson(route('articles.create'))
            ->assertForbidden()
            ->assertExactJson([
                'message' => 'Создавать новости может только модератор.',
            ]);

        $this->post(route('articles.store'), [])
            ->assertForbidden();

        $this->post(route('articles.comments.store', $article), [
            'content' => 'Комментарий читателя.',
        ])
            ->assertRedirect(route('articles.show', $article))
            ->assertSessionHas('status', 'Комментарий отправлен и ожидает модерации.');

        $this->assertDatabaseHas('comments', [
            'article_id' => $article->id,
            'user_id' => $reader->id,
            'content' => 'Комментарий читателя.',
        ]);
    }

    public function test_reader_cannot_edit_or_delete_comments(): void
    {
        $reader = User::factory()->create();
        $article = Article::factory()->create();
        $comment = $article->comments()->make(['content' => 'Комментарий.']);
        $comment->user()->associate($reader);
        $comment->save();

        $this->actingAs($reader)
            ->put(route('articles.comments.update', [$article, $comment]), [
                'content' => 'Изменённый комментарий.',
            ])
            ->assertForbidden();

        $this->delete(route('articles.comments.destroy', [$article, $comment]))
            ->assertForbidden();

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => 'Комментарий.',
        ]);
    }

    public function test_comment_cannot_be_managed_through_another_article_route(): void
    {
        $moderator = User::factory()->moderator()->create();
        $article = Article::factory()->create();
        $anotherArticle = Article::factory()->create();
        $comment = $article->comments()->make(['content' => 'Комментарий.']);
        $comment->user()->associate($moderator);
        $comment->save();

        $this->actingAs($moderator)
            ->delete(route('articles.comments.destroy', [$anotherArticle, $comment]))
            ->assertNotFound();

        $this->assertDatabaseHas('comments', ['id' => $comment->id]);
    }

    public function test_comment_validation_rejects_empty_content(): void
    {
        $reader = User::factory()->create();
        $article = Article::factory()->create();

        $this->actingAs($reader)
            ->from(route('articles.show', $article))
            ->post(route('articles.comments.store', $article), ['content' => ''])
            ->assertRedirect(route('articles.show', $article))
            ->assertSessionHasErrors([
                'content' => 'Введите текст комментария.',
            ]);

        $this->assertDatabaseCount('comments', 0);
    }

    public function test_moderator_can_manage_articles_and_comments(): void
    {
        $moderator = User::factory()->moderator()->create();
        $article = Article::factory()->create();
        $comment = $article->comments()->make(['content' => 'Комментарий.']);
        $comment->user()->associate($moderator);
        $comment->save();

        $this->actingAs($moderator)
            ->get(route('articles.index'))
            ->assertOk()
            ->assertSee(route('articles.create'), false);

        $this->put(route('articles.comments.update', [$article, $comment]), [
            'content' => 'Обновлённый комментарий.',
        ])
            ->assertRedirect(route('articles.show', $article))
            ->assertSessionHas('status', 'Комментарий успешно обновлён.');

        $this->delete(route('articles.comments.destroy', [$article, $comment]))
            ->assertRedirect(route('articles.show', $article))
            ->assertSessionHas('status', 'Комментарий успешно удалён.');

        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    public function test_role_seeders_create_roles_and_a_moderator(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseHas('roles', ['name' => Role::MODERATOR]);
        $this->assertDatabaseHas('roles', ['name' => Role::READER]);

        $moderator = User::query()->where('email', 'moderator@example.com')->firstOrFail();
        $this->assertSame(Role::MODERATOR, $moderator->role->name);
    }

    public function test_database_seeder_can_run_twice_without_duplicate_demo_records(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('users', 2);
        $this->assertDatabaseCount('articles', 20);
        $this->assertDatabaseCount('roles', 2);
    }

    public function test_database_seeder_prints_working_credentials_for_each_seeded_account(): void
    {
        Artisan::call('db:seed', ['--no-interaction' => true]);
        $output = Artisan::output();

        $this->assertMatchesRegularExpression(
            '/Аккаунт читателя: test@example\.com \| Пароль: (\S+)/',
            $output,
        );
        preg_match('/Аккаунт читателя: test@example\.com \| Пароль: (\S+)/', $output, $readerCredentials);
        preg_match('/Аккаунт модератора: moderator@example\.com \| Пароль: (\S+)/', $output, $moderatorCredentials);

        $this->assertTrue(Hash::check(
            $readerCredentials[1],
            User::query()->where('email', 'test@example.com')->firstOrFail()->password,
        ));
        $this->assertTrue(Hash::check(
            $moderatorCredentials[1],
            User::query()->where('email', 'moderator@example.com')->firstOrFail()->password,
        ));
    }

    public function test_role_policies_allow_reader_content_access_and_moderator_management(): void
    {
        $reader = User::factory()->create();
        $moderator = User::factory()->moderator()->create();
        $article = Article::factory()->create();
        $comment = $article->comments()->make(['content' => 'Комментарий.']);
        $comment->user()->associate($reader);
        $comment->save();

        $this->assertTrue($reader->can('viewAny', Article::class));
        $this->assertTrue($reader->can('view', $article));
        $this->assertFalse($reader->can('create', Article::class));
        $this->assertFalse($reader->can('update', $article));
        $this->assertFalse($reader->can('delete', $article));
        $this->assertTrue($reader->can('viewAny', Comment::class));
        $this->assertTrue($reader->can('view', $comment));
        $this->assertTrue($reader->can('create', Comment::class));
        $this->assertFalse($reader->can('update', $comment));
        $this->assertFalse($reader->can('delete', $comment));
        $this->assertFalse($reader->can('manage-articles'));
        $this->assertSame(
            'Управлять новостями может только модератор.',
            Gate::forUser($reader)->inspect('manage-articles')->message(),
        );

        $this->assertTrue($moderator->can('create', Article::class));
        $this->assertTrue($moderator->can('update', $article));
        $this->assertTrue($moderator->can('delete', $article));
        $this->assertTrue($moderator->can('update', $comment));
        $this->assertTrue($moderator->can('delete', $comment));
    }
}
