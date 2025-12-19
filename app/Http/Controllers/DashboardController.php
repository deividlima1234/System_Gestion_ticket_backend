<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function getStats(Request $request)
    {
        $user = $request->user();
        $role = $user->role;

        switch ($role) {
            case 'admin':
                return $this->getAdminStats();
            case 'support':
                return $this->getSupportStats($user);
            case 'user':
                return $this->getUserStats($user);
            default:
                return response()->json(['message' => 'Role not recognized'], 403);
        }
    }

    private function getAdminStats()
    {
        return response()->json([
            'role' => 'admin',
            'total_users' => User::count(),
            'total_tickets' => Ticket::count(),
            'tickets_open' => Ticket::where('status', 'open')->count(),
            'tickets_in_progress' => Ticket::where('status', 'in_progress')->count(),
            'tickets_by_priority' => Ticket::select('priority', DB::raw('count(*) as total'))
                ->groupBy('priority')
                ->get(),
            'recent_tickets' => Ticket::with('user:id,name,email')
                ->latest()
                ->take(5)
                ->get()
        ]);
    }

    private function getSupportStats(User $user)
    {
        $assignedTicketsQuery = Ticket::where('assigned_to', $user->id);

        $assignedTicketsCount = (clone $assignedTicketsQuery)
            ->whereIn('status', ['open', 'in_progress', 'pending'])
            ->count();

        $resolvedToday = Ticket::where('assigned_to', $user->id)
            ->whereIn('status', ['resolved', 'closed'])
            ->whereDate('updated_at', Carbon::today())
            ->count();
            
        $resolvedTotal = Ticket::where('assigned_to', $user->id)
            ->whereIn('status', ['resolved', 'closed'])
            ->count();

        $unassignedTickets = Ticket::whereNull('assigned_to')
            ->where('status', 'open')
            ->count();

        $urgentAssignedTickets = (clone $assignedTicketsQuery)
            ->where('priority', 'high')
            ->whereIn('status', ['open', 'in_progress'])
            ->get();
        
        $myAssignedTickets = (clone $assignedTicketsQuery)
             ->whereIn('status', ['open', 'in_progress', 'pending'])
             ->latest()
             ->take(10)
             ->get();

        return response()->json([
            'role' => 'support',
            'assigned_tickets_count' => $assignedTicketsCount,
            'resolved_tickets_today' => $resolvedToday,
            'resolved_tickets_total' => $resolvedTotal,
            'unassigned_tickets_count' => $unassignedTickets,
            'urgent_assigned_tickets' => $urgentAssignedTickets,
            'my_assigned_tickets' => $myAssignedTickets
        ]);
    }

    private function getUserStats(User $user)
    {
        $myOpenTickets = Ticket::where('user_id', $user->id)
            ->whereIn('status', ['open', 'in_progress', 'pending'])
            ->count();

        $myClosedTickets = Ticket::where('user_id', $user->id)
            ->whereIn('status', ['resolved', 'closed'])
            ->count();

        $recentTickets = Ticket::where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        return response()->json([
            'role' => 'user',
            'my_open_tickets' => $myOpenTickets,
            'my_closed_tickets' => $myClosedTickets,
            'recent_tickets' => $recentTickets
        ]);
    }
}
