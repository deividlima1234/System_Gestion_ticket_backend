<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Ticket;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index(Ticket $ticket)
    {
        return $ticket->comments()->with('user')->get();
    }

    public function store(Request $request, Ticket $ticket)
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        $comment = $ticket->comments()->create([
            'content' => $request->content,
            'user_id' => $request->user()->id,
        ]);

        return response()->json($comment, 201);
    }
}
