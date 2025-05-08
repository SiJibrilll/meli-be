<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Discussion;
use Illuminate\Http\Request;

class DiscussionController extends Controller
{
    function create(Request $request, $id) {
        // verify content
        $request->validate([
            'content' => 'required|string',
        ]);

        $article = Article::find($id);
        if (!$article) {
            return response()->json([
                'message' => 'Article not found',
            ], 404);
        }

        $user = $request->user();
        
        $user->discussions()->attach($article->id, [
            'content' => $request->input('content'),
        ]);

        $article->load('discussions');


        $discussion = Discussion::where('article_id', $article->id)
            ->where('user_id', $user->id)
            ->first();
        
        // formulate response
        $response = collect($discussion->toArray())->only(['id', 'content', 'article_id'])->merge([
            'author' => [
                'id' => $user->id,
                'username' => $user->username,
                'image' => $user->details->image->image ?? null,
            ]
        ]);

        return response()->json([
            'message' => 'Discussion created successfully',
            'discussions' => $response,
        ], 200);
    }
}
