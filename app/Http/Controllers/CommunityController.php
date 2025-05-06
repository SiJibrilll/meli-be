<?php

namespace App\Http\Controllers;

use App\Models\Community;
use Illuminate\Http\Request;

use function PHPSTORM_META\map;

class CommunityController extends Controller
{
    function create(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255|unique:communities,title',
            'bio' => 'nullable|string|max:1000',
            'image_id' => 'nullable|integer|exists:images,id',
        ]);

        $user = $request->user();

        //check if user is doctor
        if (!$user->hasRole('doctor')) {
            return response()->json(['message' => 'Only doctors can create communities.'], 403);
        }

        // if, doctor create community
        $community = Community::create([
            'title' => $request->input('title'),
            'bio' => $request->input('bio'),
            'user_id' => $user->id,
            'image_id' => $request->input('image_id'),
        ]);

        // formulate response
        $response = collect($community)->only([
            'id',
            'title',
            'bio',
            'members_count',
        ]);

        $response = $response->merge([
            'image' => $community->image->image ?? null,
            'owner' => [
                'id' => $user->id,
                'username' => $user->username,
                'image' => $user->details->image->image ?? null,
            ],
        ]);

        return response()->json([
            'message' => 'Community created successfully!',
            'community' => $response,
        ], 201);
        
    }

    function update(Request $request) {
        //validate request
        $request->validate([
            'title' => 'required|string|max:255|unique:communities,title',
            'bio' => 'nullable|string|max:1000',
            'image_id' => 'nullable|integer|exists:images,id',
        ]);

        $user = $request->user();
        $community = Community::find($request->id);

        //check if community exists
        if (!$community) {
            return response()->json(['message' => 'Community not found'], 404);
        }

        //check if user is owner of the community
        if ($community->user_id !== $user->id) {
            return response()->json(['message' => 'You are not the owner of this community.'], 403);
        }

        //check if user is doctor
        if (!$user->hasRole('doctor')) {
            return response()->json(['message' => 'Only doctors can update communities.'], 403);
        }

        // if, doctor update community
        $community->update([
            'title' => $request->input('title'),
            'bio' => $request->input('bio'),
            'image_id' => $request->input('image_id'),
        ]);


        // formulate response  
        $response = collect($community)->only([
            'id',
            'title',
            'bio',
            'members_count',
        ]);

        $response = $response->merge([
            'image' => $community->image->image ?? null,
            'owner' => [
                'id' => $user->id,
                'username' => $user->username,
                'image' => $user->details->image->image ?? null,
            ],
        ]);

        return response()->json([
            'message' => 'Community updated successfully!',
            'community' => $response,
        ], 200);
    }

    function delete(Request $request) {
        $user = $request->user();

        //check if user is doctor
        if (!$user->hasRole('doctor')) {
            return response()->json(['message' => 'Only doctors can delete communities.'], 403);
        }

        //check if community exists
        $community = Community::find($request->id);
        if (!$community) {
            return response()->json(['message' => 'Community not found'], 404);
        }
        //check if user is owner of the community
        if ($community->user_id !== $user->id) {
            return response()->json(['message' => 'You are not the owner of this community.'], 403);
        }

        // if all is true, delete community
        $community->delete();
        return response()->json([
            'message' => 'Community deleted successfully!',
        ], 200);
    }

    function index() {
        $communities = Community::with('image')->get();

        $response = $communities->map(function ($community) {
            return [
                'id' => $community->id,
                'title' => $community->title,
                'bio' => $community->bio,
                'members_count' => $community->members_count,
                'image' => $community->image->image ?? null,
            ];
        });

        return response()->json([
            'communities' => $response,
        ], 200);
    }

    function get($id) {
        $community = Community::with('image')->find($id);

        if (!$community) {
            return response()->json(['message' => 'Community not found'], 404);
        }

        $response = collect($community)->only([
            'id',
            'title',
            'bio',
            'members_count',
        ]);

        $response = $response->merge([
            'image' => $community->image->image ?? null,
            'owner' => [
                'id' => $community->user_id,
                'username' => $community->owner->username,
                'image' => $community->owner->details->image->image ?? null,
            ],
        ]);

        return response()->json([
            'community' => $response,
        ], 200);
    }

    function join(Request $request, $id) {
        $user = $request->user();
        $community = Community::find($id);

        if (!$community) {
            return response()->json(['message' => 'Community not found'], 404);
        }

        //check if user is already a member
        if ($community->members()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'You are already a member of this community'], 400);
        }

        //add user to community
        $community->members()->attach($user->id);
        $community->increment('members_count');

        return response()->json([
            'message' => 'You have joined the community successfully',
            'community' => [
                'members_count' => $community->members_count,
            ],
        ], 200);
        
    }

    function leave(Request $request, $id) {
        $user = $request->user();
        $community = Community::find($id);

        if (!$community) {
            return response()->json(['message' => 'Community not found'], 404);
        }

        //check if user is already a member
        if (!$community->members()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'You are not a member of this community'], 400);
        }

        //remove user from community
        $community->members()->detach($user->id);
        $community->decrement('members_count');

        return response()->json([
            'message' => 'You have left the community successfully',
            'community' => [
                'members_count' => $community->members_count,
            ],
        ], 200);
        
    }
}
