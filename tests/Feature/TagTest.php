<?php

use App\Models\Tag;
use App\Models\Task;
use App\Models\User;
use function Pest\Laravel\actingAs;

function createAuthUserForTag()
{
    $user = User::factory()->create();
    actingAs($user);
    return $user;
}

it('attaches a tag to a task and creates it if it does not exist', function () {
    $user = createAuthUserForTag();
    $task = Task::factory()->for($user)->create();

    $this->postJson("/api/tasks/{$task->id}/tags", ['name' => 'work'])
        ->assertOk()
        ->assertJsonCount(1, 'data.tags')
        ->assertJsonPath('data.tags.0.name', 'work');

    $this->assertDatabaseHas('tags', ['name' => 'work', 'user_id' => $user->id]);
});

it('prevents duplicate tag attachment', function () {
    $user = createAuthUserForTag();
    $task = Task::factory()->for($user)->create();

    $this->postJson("/api/tasks/{$task->id}/tags", ['name' => 'school'])->assertOk();
    $this->postJson("/api/tasks/{$task->id}/tags", ['name' => 'school'])->assertOk();

    $this->assertDatabaseCount('tags', 1);
    $this->assertDatabaseCount('tag_task', 1);
});

it('removes a tag from a task', function () {
    $user = createAuthUserForTag();
    $task = Task::factory()->for($user)->create();
    $tag = Tag::factory()->for($user)->create(['name' => 'home']);
    $task->tags()->attach($tag);

    $this->deleteJson("/api/tasks/{$task->id}/tags/{$tag->id}")
        ->assertOk()
        ->assertJsonCount(0, 'data.tags');

    $this->assertDatabaseMissing('tag_task', ['task_id' => $task->id, 'tag_id' => $tag->id]);
});

it('prevents removing a foreign tag', function () {
    $user = createAuthUserForTag();
    $task = Task::factory()->for($user)->create();
    $foreignTag = Tag::factory()->create();

    $this->deleteJson("/api/tasks/{$task->id}/tags/{$foreignTag->id}")->assertForbidden();
});

it('prevents attaching a tag to a foreign task', function () {
    createAuthUserForTag();
    $foreign = Task::factory()->create();

    $this->postJson("/api/tasks/{$foreign->id}/tags", ['name' => 'hack'])->assertForbidden();
});

it('lists only user owned tags', function () {
    $user = createAuthUserForTag();
    Tag::factory()->for($user)->create(['name' => 'mine']);
    Tag::factory()->create(['name' => 'foreign']);

    $this->getJson('/api/tags')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'mine');
});

it('filters tasks by tag', function () {
    $user = createAuthUserForTag();
    $tag = Tag::factory()->for($user)->create(['name' => 'work']);
    $tagged = Task::factory()->for($user)->create();
    $tagged->tags()->attach($tag);
    Task::factory()->for($user)->create();

    $this->getJson("/api/tasks?tag={$tag->id}")
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $tagged->id);
});

it('filters tasks by completion status', function () {
    $user = createAuthUserForTag();
    Task::factory()->for($user)->create(['is_completed' => true]);
    Task::factory()->count(2)->for($user)->create(['is_completed' => false]);

    $this->getJson('/api/tasks?status=completed')->assertJsonCount(1, 'data');
    $this->getJson('/api/tasks?status=pending')->assertJsonCount(2, 'data');
    $this->getJson('/api/tasks?status=invalid')->assertUnprocessable();
});

it('searches in title and description', function () {
    $user = createAuthUserForTag();
    Task::factory()->for($user)->create(['title' => 'Buy milk', 'description' => null]);
    Task::factory()->for($user)->create(['title' => 'Other item', 'description' => 'Get some bread']);
    Task::factory()->for($user)->create(['title' => 'Unrelated', 'description' => null]);

    $this->getJson('/api/tasks?search=milk')->assertJsonCount(1, 'data');
    $this->getJson('/api/tasks?search=bread')->assertJsonCount(1, 'data');
});

it('search query never returns foreign tasks', function () {
    createAuthUserForTag();
    Task::factory()->create(['title' => 'Foreign milk']);

    $this->getJson('/api/tasks?search=milk')->assertJsonCount(0, 'data');
});
