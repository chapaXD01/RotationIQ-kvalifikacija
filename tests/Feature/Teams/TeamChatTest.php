<?php

test('team members can post chat messages', function () {
    $coach = \App\Models\User::factory()->create(['role' => 'coach']);
    $student = \App\Models\User::factory()->create(['role' => 'student']);

    $team = \App\Models\Team::create([
        'coach_id' => $coach->id,
        'name' => 'Test Team',
        'join_code' => 'ABC12345',
    ]);

    $team->members()->attach($coach->id);
    $team->members()->attach($student->id);

    $response = $this->actingAs($student)->post(route('teams.messages.store', $team), [
        'message' => 'Nice rotation today!',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('team_messages', [
        'team_id' => $team->id,
        'user_id' => $student->id,
        'message' => 'Nice rotation today!',
    ]);
});

test('non team members cannot post chat messages', function () {
    $coach = \App\Models\User::factory()->create(['role' => 'coach']);
    $student = \App\Models\User::factory()->create(['role' => 'student']);
    $outsider = \App\Models\User::factory()->create(['role' => 'student']);

    $team = \App\Models\Team::create([
        'coach_id' => $coach->id,
        'name' => 'Another Team',
        'join_code' => 'XYZ98765',
    ]);

    $team->members()->attach($coach->id);
    $team->members()->attach($student->id);

    $response = $this->actingAs($outsider)->from(route('teams.index'))->post(route('teams.messages.store', $team), [
        'message' => 'I should not be allowed',
    ]);

    $response->assertForbidden();
    $this->assertDatabaseCount('team_messages', 0);
});
