<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Thread;


class CommentController extends Controller
{
    public function create(Request $request, $id) {
        $request->validate([
            'content' => 'required|string',
            'parent_id' => 'nullable|integer|exists:comments,id'
        ]);

        $thread = Thread::find($id);
        $user = $request->user();

        if (!$thread) {
            return response()->json([
                'message' => 'Thread not found',
            ], 404);
        }

        $comment = $user->comments()->create([
            'content' => $request->content,
            'parent_id' => $request->parent_id,
            'thread_id' => $thread->id
        ]);

        // formulate response
        $response = collect($comment->toArray())->only(['id', 'content', 'thread_id', 'parent_id', 'reply_count'])->merge([
            'author' => [
                'id' => $user->id,
                'username' => $user->username,
                'image' => $user->details->image->image ?? null,
            ],
            'replies' => []
        ]);

        return response()->json([
            'reply' => $response,
        ], 200);




    }
}
