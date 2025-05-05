<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;

class ArticleVerificationController extends Controller
{
    function verify(Request $request, $id) {
        $article = Article::find($id);
        $user = $request->user();
        
        if (!$article) {
            return response()->json([
                'message' => 'Article not found',
            ], 404);
        }

        // Check if the article is already verified
        if ($article->verified_by->contains($user->id)) {
            return response()->json([
                'message' => 'Article already verified',
            ], 400);
        }

        // Verify the article
        $article->verified_by()->attach($user->id);
        $article->load('verified_by');

        $verified_by = $article->verified_by->map(function ($user) {
            return [
                'id' => $user->id,
                'username' => $user->username,
                'image' => $user->details->image->image ?? null,
            ];
        });

        return response()->json([
            'message' => 'Article verified successfully',
            'verified_by' => $verified_by
        ], 200);
    }

    function unverify(Request $request, $id) {
        $article = Article::find($id);
        $user = $request->user();

        if (!$article) {
            return response()->json([
                'message' => 'Article not found',
            ], 404);
        }

        // Check if the article is not verified
        if (!$article->verified_by->contains($user->id)) {
            return response()->json([
                'message' => 'Article not verified',
            ], 400);
        }

        // Unverify the article
        $article->verified_by()->detach($user->id);
        $article->load('verified_by');
        
        $verified_by = $article->verified_by->map(function ($user) {
            return [
                'id' => $user->id,
                'username' => $user->username,
                'image' => $user->details->image->image ?? null,
            ];
        });

        return response()->json([
            'message' => 'Article unverified successfully',
            'verified_by' => $verified_by
        ], 200);
        
    }
}
