<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    function like(Request $request, $id) {
        $article = Article::find($id);
        $user = $request->user();

        if (!$article) {
            return response()->json([
                'message' => 'Article not found',
            ], 404);
        }

        // Check if the article is already liked
        if ($article->liked_by->contains($user->id)) {
            return response()->json([
                'message' => 'Article already liked',
            ], 400);
        }

        // Like the article
        $article->liked_by()->attach($user->id);
        $article->load('liked_by');
        $article->increment('like_count');
        $article->save();

        return response()->json([
            'message' => 'Article liked successfully',
            'like_count' => $article->like_count,
        ], 200);
    }

    function unlike(Request $request, $id) {
        $article = Article::find($id);
        $user = $request->user();

        if (!$article) {
            return response()->json([
                'message' => 'Article not found',
            ], 404);
        }

        // Check if the article is not liked
        if (!$article->liked_by->contains($user->id)) {
            return response()->json([
                'message' => 'Article not liked',
            ], 400);
        }

        // Unlike the article
        $article->liked_by()->detach($user->id);
        $article->load('liked_by');

        $article->decrement('like_count');
        $article->save();

        return response()->json([
            'message' => 'Article unliked successfully',
            'like_count' => $article->like_count,
        ], 200);
        
    }
}
