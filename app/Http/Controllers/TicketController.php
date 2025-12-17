<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Ticket::with('user:id,name,email');

        if ($user->role === 'admin') {
            return $query->get();
        }

        // For non-admins (user/support)
        $query->where(function ($q) {
            // Active tickets
            $q->whereIn('status', ['open', 'in_progress', 'pending', 'resolved'])
              // OR Closed tickets updated today
              ->orWhere(function ($subQ) {
                  $subQ->where('status', 'closed')
                       ->whereDate('updated_at', \Carbon\Carbon::today());
              });
        });

        if ($user->role === 'user') {
            $query->where('user_id', $user->id);
        }

        if ($user->role === 'support') {
            $query->where('assigned_to', $user->id);
        }

        return $query->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'priority' => 'in:low,medium,high',
        ]);

        $ticket = Ticket::create([
            'title' => $request->title,
            'description' => $request->description,
            'priority' => $request->priority ?? 'medium',
            'status' => 'open',
            'user_id' => $request->user()->id,
        ]);

        \Illuminate\Support\Facades\Mail::to($request->user())->send(new \App\Mail\TicketCreated($ticket));

        return response()->json($ticket, 201);
    }

    public function show(Ticket $ticket)
    {
        $user = Auth::user();

        // Access control for closed tickets
        if ($ticket->status === 'closed' && $user->role !== 'admin') {
            return response()->json(['message' => 'This ticket has been closed and archived.'], 403);
        }

        if ($user->role === 'user' && $ticket->user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return $ticket->load('user:id,name,email');
    }

    public function update(Request $request, Ticket $ticket)
    {
        $user = $request->user();

        if ($user->role === 'user') {
             return response()->json(['message' => 'Forbidden'], 403);
        }

        // If ticket is closed, check for reopening logic
        if ($ticket->status === 'closed') {
            // Only admin can modify a closed ticket
            if ($user->role !== 'admin') {
                return response()->json(['message' => 'Closed tickets cannot be modified.'], 403);
            }

            // Check if it's a reopening attempt
            if ($request->has('status') && in_array($request->status, ['open', 'in_progress'])) {
                // Allow reopening
            } else {
                return response()->json(['message' => 'Closed tickets can only be reopened.'], 403);
            }
        }

        $request->validate([
            'status' => 'in:open,in_progress,pending,resolved,closed',
            'priority' => 'in:low,medium,high',
        ]);

        $oldStatus = $ticket->status;
        $ticket->update($request->only(['status', 'priority']));

        if ($request->has('status') && $request->status !== $oldStatus) {
             \Illuminate\Support\Facades\Mail::to($ticket->user)->send(new \App\Mail\TicketStatusUpdated($ticket));
        }

        return response()->json($ticket);
    }

    public function assign(Request $request, Ticket $ticket)
    {
        $user = $request->user();

        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $assignedUser = \App\Models\User::find($request->assigned_to);

        if ($assignedUser->role !== 'support') {
             return response()->json(['message' => 'The assigned user must have the support role.'], 422);
        }

        $ticket->update(['assigned_to' => $request->assigned_to]);

        return response()->json($ticket);
    }
}
