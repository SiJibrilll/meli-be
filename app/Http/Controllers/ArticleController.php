<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\User;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    function index() {
        $articles = Article::all();
        // map article data
        $articles = $articles->map(function ($article) {
            return [
                'id' => $article->id,
                'title' => $article->title,
                'content' => $article->content,
                'image' => optional($article->image)->getPath(),
                'like_count' => $article->like_count
            ];
        });

        return response()->json([
            'articles' => $articles,
        ], 200);
    }

    function get(Request $request, $id) {
        $article = Article::find($id);
        if (!$article) {
            return response()->json([
                'message' => 'Article not found',
            ], 404);
        }

        $response = collect($article->toArray())->only(['id', 'title', 'content', 'like_count'])->merge([
            'image' => optional($article->image)->getPath()
        ]);

        $verified_by = $article->verified_by->map(function ($user) {
            return [
                'id' => $user->id,
                'username' => $user->username,
                'image' => optional($user->details->image)->getPath(),
            ];
        });

        return response()->json([
            'article' => $response,
            'author' => [
                'id' => $article->user->id,
                'username' => $article->user->username,
                'image' => optional($article->user->details->image)->getPath(),
            ],
            "verivied_by" => $verified_by, 
            "discussions" => [], //TODO add discussions
        ], 200);
    }

    function user_articles($id) {
        $users = User::find($id);
        if (!$users) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }
        $articles = $users->articles;

        // map article data
        $articles = $articles->map(function ($article) {
            return [
                'id' => $article->id,
                'title' => $article->title,
                'content' => $article->content,
                'image' => optional($article->image)->getPath(),
                'like_count' => $article->like_count
            ];
        });
        return response()->json([
            'articles' => $articles,
        ], 200);
    }


    function create(Request $request) {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image_id' => 'nullable|integer|exists:images,id',
        ]);

        $user = $request->user();

        $article =  $user->articles()->create([
            'title' => $request->title,
            'content' => $request->content,
            'image_id' => $request->image_id,
        ]);

        $response = collect($article->toArray())->only(['id', 'title', 'content'])->merge([
            'image' => optional($article->image)->getPath() ?? null
        ]);


        return response()->json([
            'message' => 'Article created successfully',
            'article' => $response,
            'author' => [
                'id' => $user->id,
                'username' => $user->username,
                'image' => optional($user->details->image)->getPath(),
            ],
            "verivied_by" => [], 
            "discussions" => [], 
        ], 201);
        
    }

    function update(Request $request, $id) {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image_id' => 'nullable|integer|exists:images,id',
        ]);

        $user = $request->user();
        $article = Article::find($id);
        if (!$article) {
            return response()->json([
                'message' => 'Article not found',
            ], 404);
        }

        if ($article->user_id !== $user->id) {
            return response()->json([
                'message' => 'You are not authorized to update this article',
            ], 403);
        }
        // TODO think of what to do with deleted images

        $article->update([
            'title' => $request->title,
            'content' => $request->content,
            'image_id' => $request->image_id,
        ]);

        $response = collect($article->toArray())->only(['id', 'title', 'content'])->merge([
            'image' => optional($article->image)->getPath() ?? null
        ]);

        $verified_by = $article->verified_by->map(function ($user) {
            return [
                'id' => $user->id,
                'username' => $user->username,
                'image' => optional($user->details->image)->getPath(),
            ];
        });

        return response()->json([
            'message' => 'Article updated successfully',
            'article' => $response,
            'author' => [
                'id' => $user->id,
                'username' => $user->username,
                'image' => optional($user->details->image)->getPath(),
            ],
            "verivied_by" => $verified_by, 
            "discussions" => [], //TODO add discussions
        ], 201);
        
    }

    function delete($id) {
        $user = request()->user();
        $article = Article::find($id);
        if (!$article) {
            return response()->json([
                'message' => 'Article not found',
            ], 404);
        }
        if ($article->user_id !== $user->id) {
            return response()->json([
                'message' => 'You are not authorized to delete this article',
            ], 403);
        }

        $article->delete();

        return response()->json([
            'message' => 'Article deleted successfully',
        ], 200);
    }
}
