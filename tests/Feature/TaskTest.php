<?php

use App\Models\Task;
use App\Models\User;
use function Pest\Laravel\actingAs;

function createAuthUser()
{
    $user = User::factory()->create();
    actingAs($user);
    return $user;
}

it('creates a task', function () {
    $user = createAuthUser();

    $response = $this->postJson('/api/tasks', [
        'title' => 'New task',
        'description' => 'Task description',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.title', 'New task')
        ->assertJsonPath('data.is_completed', false);

    $this->assertDatabaseHas('tasks', [
        'title' => 'New task',
        'user_id' => $user->id,
    ]);
});

it('does not create a task without a title', function () {
    createAuthUser();

    $this->postJson('/api/tasks', ['description' => 'Only description'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('title');

    $this->assertDatabaseCount('tasks', 0);
});

it('updates task title and description', function () {
    $user = createAuthUser();
    $task = Task::factory()->for($user)->create();

    $this->putJson("/api/tasks/{$task->id}", [
        'title' => 'Updated title',
        'description' => 'Updated description',
    ])
        ->assertOk()
        ->assertJsonPath('data.title', 'Updated title');

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'title' => 'Updated title',
        'description' => 'Updated description',
    ]);
});

it('deletes a task', function () {
    $user = createAuthUser();
    $task = Task::factory()->for($user)->create();

    $this->deleteJson("/api/tasks/{$task->id}")->assertNoContent();

    $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
});

it('toggles task completion status', function () {
    $user = createAuthUser();
    $task = Task::factory()->for($user)->create(['is_completed' => false]);

    $this->patchJson("/api/tasks/{$task->id}/complete")
        ->assertOk()
        ->assertJsonPath('data.is_completed', true);

    $this->patchJson("/api/tasks/{$task->id}/complete")
        ->assertOk()
        ->assertJsonPath('data.is_completed', false);
});

it('fetches a single task', function () {
    $user = createAuthUser();
    $task = Task::factory()->for($user)->create();

    $this->getJson("/api/tasks/{$task->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $task->id);
});

it('prevents foreign users from interacting with my task', function () {
    createAuthUser();
    $foreign = Task::factory()->create();

    $this->getJson("/api/tasks/{$foreign->id}")->assertForbidden();
    $this->putJson("/api/tasks/{$foreign->id}", ['title' => 'Hack'])->assertForbidden();
    $this->patchJson("/api/tasks/{$foreign->id}/complete")->assertForbidden();
    $this->deleteJson("/api/tasks/{$foreign->id}")->assertForbidden();

    $this->assertDatabaseHas('tasks', ['id' => $foreign->id, 'title' => $foreign->title]);
});

it('returns only user owned tasks in list', function () {
    $user = createAuthUser();
    Task::factory()->count(2)->for($user)->create();
    Task::factory()->count(3)->create();

    $this->getJson('/api/tasks')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

it('paginates task list by 10 items', function () {
    $user = createAuthUser();
    Task::factory()->count(15)->for($user)->create();

    $this->getJson('/api/tasks')
        ->assertOk()
        ->assertJsonCount(10, 'data')
        ->assertJsonPath('meta.total', 15);
});
