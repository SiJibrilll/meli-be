<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ArticleVerificationController extends Controller
{
    function verify() {
        return response()->json([
            'message' => 'Article verified successfully',
        ], 200);
    }

    function unverify() {
        return response()->json([
            'message' => 'Article unverified successfully',
        ], 200);
        
    }
}
