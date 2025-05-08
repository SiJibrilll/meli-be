<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Discussion;
use App\Models\User;
use Illuminate\Http\Request;

class DiscussionController extends Controller
{
    public function get($id) {
        $discussion = Discussion::find($id);

        if (!$discussion) {
            return response()->json([
                'message' => 'Discussion not found',
            ], 404);
        }

        $author = User::find($discussion->user_id);


        // formulate response
        $response = collect($discussion->toArray())->only(['id', 'content', 'article_id'])->merge([
            'author' => [
                'id' => $author->id,
                'username' => $author->username,
                'image' => optional($author->details->image)->getPath() ?? null,
            ]
        ]);

        return response()->json([
            'discussion' => $response,
        ], 200);

    }

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

        $discussion = Discussion::create([
            'user_id' => $user->id,
            'article_id' => $article->id,
            'content' => $request->content
        ]);

        // formulate response
        $response = collect($discussion->toArray())->only(['id', 'content', 'article_id'])->merge([
            'author' => [
                'id' => $user->id,
                'username' => $user->username,
                'image' => optional($user->details->image)->getPath() ?? null,
            ]
        ]);

        return response()->json([
            'message' => 'Discussion created successfully',
            'discussions' => $response,
        ], 200);
    }

    public function edit(Request $request, $id) {
        // verify content
        $request->validate([
            'content' => 'required|string',
        ]);

        $user = $request->user();

        $discussions = Discussion::find($id);
        if (!$discussions) {
            return response()->json([
                'message' => 'Discussion not found',
            ], 404);
        }


        //check if user owns discussion
        if ($discussions->user_id != $request->user()->id) {
            return response()->json(['message' => 'You are not authorized to update this discussion.'], 403);
        }



        $discussions->content = $request->content;
        $discussions->save();

        // formulate response
        $response = collect($discussions->toArray())->only(['id', 'content', 'article_id'])->merge([
            'author' => [
                'id' => $user->id,
                'username' => $user->username,
                'image' => optional($user->details->image)->getPath() ?? null,
            ]
        ]);

        return response()->json([
            'message' => 'Discussion updated successfully',
            'discussions' => $response,
        ], 200);

    }

    public function delete(Request $request, $id) {
        $user = $request->user();
        $discussion = Discussion::find($id);

        if (!$discussion) {
            return response()->json([
                'message' => 'Discussion not found'
            ], 404);
        }

        if ($discussion->user_id != $user->id) {
            return response()->json(['message' => 'You are not authorized to delete this discussion.'], 403);
        }

        $discussion->delete();

        return response()->json([
            'message' => "Discussion deleted successfully"
        ]);
    }
}
