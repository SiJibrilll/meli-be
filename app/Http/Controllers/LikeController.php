<?php

namespace App\Http\Controllers;

use App\Models\Thread;
use App\Models\Article;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    function likeArticle(Request $request, $id) {
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

    function unlikeArticle(Request $request, $id) {
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

    function likeThread(Request $request, $id) {
        // Similar to likeArticle but for threads
        $thread = Thread::find($id);
        $user = $request->user();
        if (!$thread) {
            return response()->json([
                'message' => 'Thread not found',
            ], 404);
        }
        // Check if the thread is already liked
        if ($thread->liked_by->contains($user->id)) {
            return response()->json([
                'message' => 'Thread already liked',
            ], 400);
        }
        // Like the thread
        $thread->liked_by()->attach($user->id);
        $thread->load('liked_by');
        $thread->increment('likes_count');
        $thread->save();

        return response()->json([
            'message' => 'Thread liked successfully',
            'likes_count' => $thread->likes_count,
        ], 200);
    }


    function unlikeThread(Request $request, $id) {
        // Similar to unlikeArticle but for threads
        $thread = Thread::find($id);
        $user = $request->user();
        if (!$thread) {
            return response()->json([
                'message' => 'Thread not found',
            ], 404);
        }
        // Check if the thread is not liked
        if (!$thread->liked_by->contains($user->id)) {
            return response()->json([
                'message' => 'Thread not liked',
            ], 400);
        }
        // Unlike the thread
        $thread->liked_by()->detach($user->id);
        $thread->load('liked_by');

        $thread->decrement('likes_count');
        $thread->save();

        return response()->json([
            'message' => 'Thread unliked successfully',
            'likes_count' => $thread->likes_count,
        ], 200);
    }
}
