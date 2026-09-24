<?php

use App\Models\User;

it('registers a user and returns a token', function () {
    $this->postJson('/api/register', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'secretpassword',
    ])
        ->assertCreated()
        ->assertJsonStructure(['user', 'token']);

    $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
});

it('does not register a user with a duplicate email', function () {
    User::factory()->create(['email' => 'john@example.com']);

    $this->postJson('/api/register', [
        'name' => 'John',
        'email' => 'john@example.com',
        'password' => 'secretpassword',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');
});

it('logs in a user with correct credentials', function () {
    User::factory()->create(['email' => 'john@example.com']);

    $this->postJson('/api/login', [
        'email' => 'john@example.com',
        'password' => 'password',
    ])
        ->assertOk()
        ->assertJsonStructure(['user', 'token']);
});

it('rejects login with invalid password', function () {
    User::factory()->create(['email' => 'john@example.com']);

    $this->postJson('/api/login', [
        'email' => 'john@example.com',
        'password' => 'wrong-password',
    ])
        ->assertUnprocessable();
});

it('logs out a user and revokes token', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $this->withToken($token)
        ->postJson('/api/logout')
        ->assertNoContent();

    $this->assertDatabaseCount('personal_access_tokens', 0);
});

it('denies unauthenticated users access to tasks', function () {
    $this->getJson('/api/tasks')->assertUnauthorized();
    $this->postJson('/api/tasks', ['title' => 'X'])->assertUnauthorized();
});
