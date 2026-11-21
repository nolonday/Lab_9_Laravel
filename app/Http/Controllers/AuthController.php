<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ], [
            'name.required' => 'Укажите имя.',
            'name.max' => 'Имя не должно превышать 255 символов.',
            'email.required' => 'Укажите email.',
            'email.email' => 'Введите корректный email.',
            'email.unique' => 'Этот email уже зарегистрирован.',
            'password.required' => 'Укажите пароль.',
            'password.min' => 'Пароль должен содержать не менее 8 символов.',
        ]);

        $user = new User($validated);
        $user->role()->associate(Role::query()->firstOrCreate(
            ['name' => Role::READER],
            ['display_name' => 'Читатель'],
        ));
        $user->save();

        return redirect()
            ->route('login')
            ->with('status', 'Регистрация завершена. Войдите в аккаунт.');
    }

    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Укажите email.',
            'email.email' => 'Введите корректный email.',
            'password.required' => 'Укажите пароль.',
        ]);

        if (! Auth::guard('web')->attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'Неверный email или пароль.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->route('home');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
