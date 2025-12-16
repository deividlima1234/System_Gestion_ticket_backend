<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TicketStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_ticket_creation_defaults_to_open()
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->postJson('/api/tickets', [
            'title' => 'Test Ticket',
            'description' => 'Test Description',
            'priority' => 'medium',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('tickets', [
            'title' => 'Test Ticket',
            'status' => 'open',
        ]);
    }

    public function test_admin_can_update_status_to_pending_and_resolved()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $ticket = Ticket::factory()->create(['status' => 'open']);

        // Update to pending
        $response = $this->actingAs($admin)->putJson("/api/tickets/{$ticket->id}", [
            'status' => 'pending',
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('tickets', ['id' => $ticket->id, 'status' => 'pending']);

        // Update to resolved
        $response = $this->actingAs($admin)->putJson("/api/tickets/{$ticket->id}", [
            'status' => 'resolved',
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('tickets', ['id' => $ticket->id, 'status' => 'resolved']);
    }

    public function test_invalid_status_update_is_rejected()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $ticket = Ticket::factory()->create(['status' => 'open']);

        $response = $this->actingAs($admin)->putJson("/api/tickets/{$ticket->id}", [
            'status' => 'invalid_status',
        ]);

        $response->assertStatus(422);
    }

    public function test_only_admin_can_assign_tickets()
    {
        $user = User::factory()->create(['role' => 'user']);
        $support = User::factory()->create(['role' => 'support']);
        $ticket = Ticket::factory()->create();

        // User tries to assign
        $response = $this->actingAs($user)->putJson("/api/tickets/{$ticket->id}/assign", [
            'assigned_to' => $support->id,
        ]);
        $response->assertStatus(403);

        // Support tries to assign
        $response = $this->actingAs($support)->putJson("/api/tickets/{$ticket->id}/assign", [
            'assigned_to' => $support->id,
        ]);
        $response->assertStatus(403);
    }

    public function test_admin_can_assign_ticket_to_support()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $support = User::factory()->create(['role' => 'support']);
        $ticket = Ticket::factory()->create();

        $response = $this->actingAs($admin)->putJson("/api/tickets/{$ticket->id}/assign", [
            'assigned_to' => $support->id,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'assigned_to' => $support->id,
        ]);
    }

    public function test_admin_cannot_assign_ticket_to_non_support_user()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        $ticket = Ticket::factory()->create();

        $response = $this->actingAs($admin)->putJson("/api/tickets/{$ticket->id}/assign", [
            'assigned_to' => $user->id,
        ]);

        $response->assertStatus(422);
    }
}
