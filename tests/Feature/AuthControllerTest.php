<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_page_renders_csrf_protected_form(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertViewIs('auth.register')
            ->assertSee('method="POST"', false)
            ->assertSee('action="'.route('register.store').'"', false)
            ->assertSee('name="_token"', false)
            ->assertSee('name="name"', false)
            ->assertSee('name="email"', false)
            ->assertSee('name="password"', false)
            ->assertSee('href="'.route('login').'"', false);
    }

    public function test_valid_registration_persists_a_hashed_password_and_redirects_to_login(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Иван Иванов',
            'email' => 'ivan@example.com',
            'password' => 'secure-password',
        ]);

        $response
            ->assertRedirect(route('login'))
            ->assertSessionHas('status', 'Регистрация завершена. Войдите в аккаунт.');

        $this->assertDatabaseHas('users', [
            'name' => 'Иван Иванов',
            'email' => 'ivan@example.com',
        ]);

        $user = User::query()->where('email', 'ivan@example.com')->firstOrFail();

        $this->assertTrue(Hash::check('secure-password', $user->password));
        $this->assertSame(Role::READER, $user->role->name);
    }

    public function test_invalid_registration_returns_field_errors_without_creating_a_user(): void
    {
        $this->from(route('register'))->post(route('register.store'), [
            'name' => '',
            'email' => 'not-an-email',
            'password' => 'short',
        ])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors([
                'name' => 'Укажите имя.',
                'email' => 'Введите корректный email.',
                'password' => 'Пароль должен содержать не менее 8 символов.',
            ]);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_registration_rejects_an_existing_email(): void
    {
        User::factory()->create(['email' => 'ivan@example.com']);

        $this->post(route('register.store'), [
            'name' => 'Иван Иванов',
            'email' => 'ivan@example.com',
            'password' => 'secure-password',
        ])
            ->assertSessionHasErrors([
                'email' => 'Этот email уже зарегистрирован.',
            ]);

        $this->assertDatabaseCount('users', 1);
    }

    public function test_login_page_renders_csrf_protected_form(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertViewIs('auth.login')
            ->assertSee('method="POST"', false)
            ->assertSee('action="'.route('login.store').'"', false)
            ->assertSee('name="_token"', false)
            ->assertSee('name="email"', false)
            ->assertSee('name="password"', false);
    }

    public function test_valid_login_authenticates_the_user_and_redirects_home(): void
    {
        $user = User::factory()->create([
            'email' => 'ivan@example.com',
            'password' => Hash::make('secure-password'),
        ]);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'secure-password',
        ])
            ->assertRedirect(route('home'));

        $this->get(route('articles.index'))->assertOk();
    }

    public function test_invalid_login_does_not_authenticate_the_user(): void
    {
        $user = User::factory()->create([
            'email' => 'ivan@example.com',
            'password' => Hash::make('secure-password'),
        ]);

        $this->from(route('login'))->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'incorrect-password',
        ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors([
                'email' => 'Неверный email или пароль.',
            ]);

        $this->get(route('articles.index'))->assertRedirect(route('login'));
    }

    public function test_logout_invalidates_session_and_regenerates_csrf_token(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withSession(['logout_marker' => 'present'])
            ->get(route('home'))
            ->assertOk();

        $previousToken = session()->token();

        $this->post(route('logout'))
            ->assertRedirect(route('home'))
            ->assertSessionMissing('logout_marker')
            ->assertSessionHas('_token');

        $this->assertNotSame($previousToken, session()->token());
        $this->assertTrue(Auth::guard('web')->guest());
    }

    public function test_guest_is_redirected_to_login_from_a_protected_route(): void
    {
        $this->get(route('articles.index'))
            ->assertRedirect(route('login'));
    }

    public function test_guest_is_redirected_to_login_from_the_gallery_route(): void
    {
        $this->get(route('galery', 1))
            ->assertRedirect(route('login'));
    }
}
