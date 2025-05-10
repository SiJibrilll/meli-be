<?php

namespace App\Http\Controllers;

use App\Models\User;
use GuzzleHttp\Psr7\Query;
use Illuminate\Http\Request;
use App\Http\Resources\userResource;
use Spatie\QueryBuilder\QueryBuilder;

class FollowController extends Controller
{
    function index() {
        $user = User::all();
        
        // map user data
        $users = $user->map(function ($user) {
            return new userResource($user);
        });

        return response()->json([
            'users' => $users,
        ], 200);

    }


    function follows($id) {
        $user = QueryBuilder::for(User::class)
            ->where('id', $id)
            ->first();
        
        // map follows data
        $follows = $user->following->map(function ($follows) {
            return new userResource($follows);
        });
            
        // map followers data
        $follwers = $user->followers->map(function ($follower) {
            return [
                'username' => $follower->username,
                'image' => optional($follower->details->image)->getPath() ?? null,
                'id' => $follower->id
            ];
        });;

        return response()->json([
            'followed_by' => $follwers,
            'follows' => $follows,
        ], 200);
        
    }

    function follow(Request $request, $id) {
        $user = QueryBuilder::for(User::class)
            ->where('id', $id)
            ->first();
        
        $follower = $request->user();

        if ($follower->following()->where('followed_id', $user->id)->exists()) {
            $follower->following()->detach($user->id);
            $user->details->decrement('follower_count');

            return response()->json([
                'message' => 'You are no longer following this user',
            ], 200);
        }

        $follower->following()->attach($user->id);
        $user->details->increment('follower_count');

        $response = collect($user->toArray())
            ->only(['id', 'username'])
            ->merge([
                'image' => optional($user->details->image)->getPath() ?? null,
            ]);

        return response()->json([
            'message' => 'You are now following this user',
            'followed_user' => $response
        ], 200);
        
    }
}
