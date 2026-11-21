<?php

namespace App\Http\Requests;

use App\Models\Article;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class ArticleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $article = $this->route('article');

        Gate::authorize($article === null ? 'create' : 'update', $article ?? Article::class);

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'published_at' => ['nullable', 'date'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Укажите заголовок новости.',
            'title.string' => 'Заголовок новости должен быть текстом.',
            'title.max' => 'Заголовок новости не должен быть длиннее :max символов.',
            'content.required' => 'Укажите текст новости.',
            'content.string' => 'Текст новости должен быть строкой.',
            'published_at.date' => 'Укажите корректную дату публикации.',
        ];
    }
}
