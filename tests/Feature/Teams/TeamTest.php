<?php

use App\Models\Team;
use App\Models\User;

test('coaches can create teams and are added as members', function () {
    $coach = User::factory()->create(['role' => 'coach']);

    $response = $this->actingAs($coach)->post(route('teams.store'), [
        'name' => 'Varsity A',
    ]);

    $response->assertRedirect(route('teams.index'));
    $this->assertDatabaseHas('teams', [
        'name' => 'Varsity A',
        'coach_id' => $coach->id,
    ]);
    $this->assertDatabaseHas('team_user', ['user_id' => $coach->id]);
});

test('students cannot create teams', function () {
    $student = User::factory()->create(['role' => 'student']);

    $this->actingAs($student)
        ->post(route('teams.store'), ['name' => 'Student Team'])
        ->assertForbidden();

    $this->assertDatabaseCount('teams', 0);
});

test('students can join a team with a coach supplied code', function () {
    $coach = User::factory()->create(['role' => 'coach']);
    $student = User::factory()->create(['role' => 'student']);
    $team = Team::create([
        'coach_id' => $coach->id,
        'name' => 'Varsity A',
        'join_code' => 'TEAM1234',
    ]);

    $response = $this->actingAs($student)->post(route('teams.join'), [
        'join_code' => 'team1234',
    ]);

    $response->assertRedirect(route('teams.index'));
    $this->assertDatabaseHas('team_user', [
        'team_id' => $team->id,
        'user_id' => $student->id,
    ]);
});

test('teams page is available to authenticated users', function () {
    $student = User::factory()->create();

    $this->actingAs($student)
        ->get(route('teams.index'))
        ->assertOk()
        ->assertSee('Teams')
        ->assertSee('Enter your coach')
        ->assertDontSee('Create a team');
});