<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\File;
use JsonException;
use UnexpectedValueException;

class MainController extends Controller
{
    public function index(): View
    {
        return view('pages.home', [
            'articles' => $this->articles(),
        ]);
    }

    public function galery(int $article): View
    {
        $articles = $this->articles();

        abort_unless(array_key_exists($article, $articles), 404);

        return view('pages.galery', [
            'article' => $articles[$article],
        ]);
    }

    /**
     * @throws JsonException
     */
    private function articles(): array
    {
        $articles = json_decode(File::get(public_path('articles.json')), true, 512, JSON_THROW_ON_ERROR);

        if (! is_array($articles) || ! array_is_list($articles)) {
            throw new UnexpectedValueException('The articles.json file must contain a JSON list.');
        }

        return $articles;
    }
}
