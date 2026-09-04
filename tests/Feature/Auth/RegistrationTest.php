<?php

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'role' => 'coach',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'role' => 'coach',
    ]);
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('registration rejects an invalid role', function () {
    $response = $this->from('/register')->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'role' => 'admin',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect('/register');
    $response->assertSessionHasErrors('role');
    $this->assertGuest();
});
