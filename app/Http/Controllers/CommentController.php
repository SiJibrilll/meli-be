<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Thread;
use App\Models\Comment;
use Illuminate\Http\Request;


class CommentController extends Controller
{
    function get($id) {
        $comment = Comment::find($id);
        if (!$comment) {
            return response()->json([
                'message' => 'Comment not found',
            ], 404);
        }

        $author = User::find($comment->user_id);

        // formulate response
        $response = collect($comment->toArray())->only(['id', 'content', 'thread_id', 'parent_id', 'reply_count'])->merge([
            'author' => [
                'id' => $author->id,
                'username' => $author->username,
                'image' => optional($author->details->image)->getPath() ?? null,
            ],
            'replies' => $comment->comments->map(function ($reply) {
                return collect($reply->toArray())->only(['id', 'content', 'thread_id', 'parent_id', 'reply_count'])->merge([
                    'author' => [
                        'id' => $reply->user->id,
                        'username' => $reply->user->username,
                        'image' => $reply->user->details->image->image ?? null,
                    ],
                ]);
            }),
        ]);

        return response()->json([
            'comment' => $response,
        ], 200);
    }

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

        // add reply count to parent comment
        if ($request->parent_id) {
            $parent_comment = Comment::find($request->parent_id);
            if ($parent_comment) {
                $parent_comment->reply_count += 1;
                $parent_comment->save();
            }
        }

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
            'comment' => $response,
        ], 200);
    }

    function edit(Request $request, $id) {
        $request->validate([
            'content' => 'required|string',
        ]);

        $user = $request->user();
        $comment = Comment::find($id);
        if (!$comment) {
            return response()->json([
                'message' => 'Comment not found',
            ], 404);
        }

        // check if user is author of comment
        if ($comment->user_id != $user->id) {
            return response()->json([
                'message' => 'You are not authorized to edit this comment',
            ], 403);
        }

        $comment->content = $request->content;
        $comment->save();

        // formulate response
        $response = collect($comment->toArray())->only(['id', 'content', 'thread_id', 'parent_id', 'reply_count'])->merge([
            'author' => [
                'id' => $user->id,
                'username' => $user->username,
                'image' => $user->details->image->image ?? null,
            ],
            'replies' => $comment->comments->map(function ($reply) {
                return collect($reply->toArray())->only(['id', 'content', 'thread_id', 'parent_id', 'reply_count'])->merge([
                    'author' => [
                        'id' => $reply->user->id,
                        'username' => $reply->user->username,
                        'image' => $reply->user->details->image->image ?? null,
                    ],
                ]);
            }),
        ]);

        return response()->json([
            'message' => 'Comment updated successfully',
            'comment' => $response,
        ], 200);
        
    }

    function delete($id) {
        $user = request()->user();
        $comment = Comment::find($id);

        if (!$comment) {
            return response()->json([
                'message' => 'Comment not found'
            ], 404);
        }

        // check if user is author of comment
        if ($comment->user_id != $user->id) {
            return response()->json([
                'message' => 'You are not authorized to delete this comment',
            ], 403);
        }

        // remove reply count from parent comment
        if ($comment->parent_id) {
            $parent_comment = Comment::find($comment->parent_id);
            if ($parent_comment) {
                $parent_comment->reply_count -= 1;
                $parent_comment->save();
            }
        }

        // delete comment
        $comment->delete();

        return response()->json([
            'message' => 'Comment deleted successfully',
        ], 200);
    }
}
