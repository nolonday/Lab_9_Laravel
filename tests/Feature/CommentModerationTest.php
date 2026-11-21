<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentModerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_comment_waits_for_moderation_and_is_not_publicly_visible(): void
    {
        $reader = User::factory()->create();
        $article = Article::factory()->create();

        $response = $this->actingAs($reader)
            ->post(route('articles.comments.store', $article), [
                'content' => 'Комментарий на проверке.',
            ]);

        $response
            ->assertRedirect(route('articles.show', $article))
            ->assertSessionHas('status', 'Комментарий отправлен и ожидает модерации.');

        $this->assertDatabaseHas('comments', [
            'article_id' => $article->id,
            'user_id' => $reader->id,
            'content' => 'Комментарий на проверке.',
            'is_approved' => false,
        ]);

        $this->get(route('articles.show', $article))
            ->assertOk()
            ->assertSee('Комментарий отправлен и ожидает модерации.')
            ->assertDontSee('Комментарий на проверке.');
    }

    public function test_article_page_displays_only_approved_comments(): void
    {
        $reader = User::factory()->create();
        $article = Article::factory()->create();
        $this->createComment($article, $reader, 'Опубликованный комментарий.', true);
        $this->createComment($article, $reader, 'Комментарий на модерации.');

        $this->actingAs($reader)
            ->get(route('articles.show', $article))
            ->assertOk()
            ->assertSee('Опубликованный комментарий.')
            ->assertDontSee('Комментарий на модерации.');
    }

    public function test_moderator_can_view_pending_comments_and_approve_one(): void
    {
        $moderator = User::factory()->moderator()->create();
        $reader = User::factory()->create();
        $article = Article::factory()->create();
        $pendingContent = 'Комментарий <script>alert(1)</script>';
        $pendingComment = $this->createComment($article, $reader, $pendingContent);
        $this->createComment($article, $reader, 'Уже опубликованный комментарий.', true);

        $this->actingAs($moderator)
            ->get(route('moderation.comments.index'))
            ->assertOk()
            ->assertViewIs('comments.moderation')
            ->assertSee($pendingContent)
            ->assertDontSee($pendingContent, false)
            ->assertDontSee('Уже опубликованный комментарий.')
            ->assertSee(route('moderation.comments.index'), false)
            ->assertSee(route('moderation.comments.approve', $pendingComment), false)
            ->assertSee(route('moderation.comments.reject', $pendingComment), false)
            ->assertSee('Одобрить')
            ->assertSee('Отклонить');

        $this->post(route('moderation.comments.approve', $pendingComment))
            ->assertRedirect(route('moderation.comments.index'))
            ->assertSessionHas('status', 'Комментарий одобрен и опубликован.');

        $this->assertDatabaseHas('comments', [
            'id' => $pendingComment->id,
            'is_approved' => true,
        ]);

        $this->get(route('articles.show', $article))
            ->assertOk()
            ->assertSee($pendingContent);
    }

    public function test_moderator_can_reject_and_remove_a_pending_comment(): void
    {
        $moderator = User::factory()->moderator()->create();
        $reader = User::factory()->create();
        $article = Article::factory()->create();
        $comment = $this->createComment($article, $reader, 'Комментарий для отклонения.');

        $this->actingAs($moderator)
            ->delete(route('moderation.comments.reject', $comment))
            ->assertRedirect(route('moderation.comments.index'))
            ->assertSessionHas('status', 'Комментарий отклонён и удалён.');

        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    public function test_reader_cannot_view_or_process_pending_comments(): void
    {
        $reader = User::factory()->create();
        $commentAuthor = User::factory()->create();
        $article = Article::factory()->create();
        $comment = $this->createComment($article, $commentAuthor, 'Закрытый для читателей комментарий.');

        $this->get(route('moderation.comments.index'))
            ->assertRedirect(route('login'));

        $this->actingAs($reader)
            ->get(route('moderation.comments.index'))
            ->assertForbidden();

        $this->get(route('articles.index'))
            ->assertOk()
            ->assertDontSee(route('moderation.comments.index'), false);

        $this->post(route('moderation.comments.approve', $comment))
            ->assertForbidden();

        $this->delete(route('moderation.comments.reject', $comment))
            ->assertForbidden();

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'is_approved' => false,
        ]);
    }

    private function createComment(
        Article $article,
        User $user,
        string $content,
        bool $isApproved = false,
    ): Comment {
        $comment = $article->comments()->make(['content' => $content]);
        $comment->user()->associate($user);
        $comment->is_approved = $isApproved;
        $comment->save();

        return $comment;
    }
}
