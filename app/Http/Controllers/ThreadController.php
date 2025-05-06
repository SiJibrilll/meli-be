<?php

namespace App\Http\Controllers;

use App\Models\Thread;
use App\Models\Community;
use Illuminate\Http\Request;

class ThreadController extends Controller
{
    function create(Request $request) {
        //validate request
        $request->validate([
            'content' => 'required|string|max:1000',
            'community_id' => 'nullable|integer|exists:communities,id',
            'image_id' => 'nullable|integer|exists:images,id',
        ]);

        $user = $request->user();

        $thread = $user->threads()->create([
            'content' => $request->input('content'),
            'likes_count' => 0,
            'image_id' => $request->input('image_id'),
        ]);

        $thread->fresh();

        //check if community id provided
        if ($request->community_id) {
            //check if user is member of community
            $community = $user->communities()->where('community_id', $request->community_id)->first();
            if (!$community) {
                $thread->delete();
                return response()->json(['message' => 'You are not a member of this community.'], 403);
            }

            $community->threads()->save($thread);
        }

        return response()->json([
            'message' => 'Thread created successfully!',
            'thread' => [
                'id' => $thread->id,
                'content' => $thread->content,
                'image' => $thread->image->image ?? null,
                'community_id' => $request->community_id,
                'likes_count' => $thread->likes_count,
                'author' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'image' => $user->details->image->image ?? null,
                ],
            ],
        ], 201);
    }

    function update(Request $request) {
        //validate request
        $request->validate([
            'content' => 'required|string|max:1000',
            'image_id' => 'nullable|integer|exists:images,id',
        ]);

        $user = $request->user();
        $thread = Thread::find($request->id);
        if (!$thread) {
            return response()->json(['message' => 'Thread not found.'], 404);
        }

        if ($thread->user_id !== $user->id) {
            return response()->json(['message' => 'You are not authorized to update this thread.'], 403);
        }

        $thread->update([
            'content' => $request->input('content'),
            'image_id' => $request->input('image_id'),
        ]);

        return response()->json([
            'message' => 'Thread updated successfully!',
            'thread' => [
                'id' => $thread->id,
                'content' => $thread->content,
                'image' => $thread->image->image ?? null,
                'community_id' => $thread->community_id,
                'likes_count' => $thread->likes_count,
                'author' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'image' => $user->details->image->image ?? null,
                ],
            ],
        ], 200);
    }

    function delete(Request $request, $id) {
        $thread = Thread::find($id);
        if (!$thread) {
            return response()->json(['message' => 'Thread not found.'], 404);
        }

        $user = $request->user();
        if ($thread->user_id !== $user->id) {
            return response()->json(['message' => 'You are not authorized to delete this thread.'], 403);
        }

        $thread->delete();
        return response()->json(['message' => 'Thread deleted successfully!'], 200);
    }

    function index() {
        $threads = Thread::all();
        $response = $threads->map(function ($thread) {
            return [
                'id' => $thread->id,
                'content' => $thread->content,
                'image' => $thread->image->image ?? null,
                'likes_count' => $thread->likes_count,
                'author' => [
                    'id' => $thread->user_id,
                    'username' => $thread->author->username,
                    'image' => $thread->author->details->image->image ?? null,
                ],
            ];
        });

        return response()->json([
            'threads' => $response,
        ], 200);
    }

    function get(Request $request) {
        $thread = Thread::find($request->id);
        if (!$thread) {
            return response()->json(['message' => 'Thread not found.'], 404);
        }

        $response = [
            'thread' => [
                'id' => $thread->id,
                'content' => $thread->content,
                'image' => $thread->image->image ?? null,
                'likes_count' => $thread->likes_count,
            ],
            'author' => [
                'id' => $thread->user_id,
                'username' => $thread->author->username,
                'image' => $thread->author->details->image->image ?? null,
            ],
            'comments' => [] //TODO add comments
        ];

        return response()->json([
            'thread' => $response,
        ], 200);
    }

    function communities_threads(Request $request, $id) {
        $community = Community::find($id);
        if (!$community) {
            return response()->json(['message' => 'Community not found.'], 404);
        }

        $threads = $community->threads;
        $response = $threads->map(function ($thread) {
            return [
                'id' => $thread->id,
                'content' => $thread->content,
                'image' => $thread->image->image ?? null,
                'likes_count' => $thread->likes_count,
                'author' => [
                    'id' => $thread->user_id,
                    'username' => $thread->author->username,
                    'image' => $thread->author->details->image->image ?? null,
                ],
            ];
        });
        return response()->json([
            'threads' => $response,
        ], 200);
    }

    
}
