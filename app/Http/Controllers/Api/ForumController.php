<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Forum;
use App\Models\Thread;
use App\Models\Threadreply;
use Illuminate\Http\Request;

class ForumController extends Controller
{
    public function forums()
    {
        $forums = Forum::with('threads')->get();
        return response()->json($forums);
    }

    public function threads()
    {
        $threads = Thread::with('threadreplys')->get();
        return response()->json($threads);
    }

    public function threadreply(Request $request,$thread_id)
    {
        $validatedData = $request->validate([
            'response' => 'required',
        ]);

        $threadreply = Threadreply::create([
            'response' => $validatedData['response'],
            'thread_id' => $thread_id,
            'user_id' => auth()->user()->id
        ]);

        return response()->json([
            'message' => 'Threadreply make success',
            'threadreply' => $threadreply,
        ], 201);
    }

}
