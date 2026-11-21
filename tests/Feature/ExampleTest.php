<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_home_page_displays_articles_from_json_with_gallery_links(): void
    {
        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertSee('What is Lorem Ipsum?')
            ->assertSee('Why do we use it?')
            ->assertSee(route('galery', ['article' => 0]), false)
            ->assertSee(asset('preview_2.jpg'), false);
    }

    public function test_gallery_page_displays_the_full_image_for_the_selected_article(): void
    {
        $this->actingAs(User::factory()->make());

        $response = $this->get(route('galery', ['article' => 0]));

        $response
            ->assertOk()
            ->assertSee('What is Lorem Ipsum?')
            ->assertSee(asset('full_2.jpeg'), false)
            ->assertSee('Все публикации');
    }

    public function test_gallery_returns_not_found_for_an_unknown_article(): void
    {
        $this->actingAs(User::factory()->make());

        $this->get(route('galery', ['article' => 99]))->assertNotFound();
    }
}
