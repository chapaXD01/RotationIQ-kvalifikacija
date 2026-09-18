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

test('coach can assign a team member as assistant manager', function () {
    $coach = \App\Models\User::factory()->create(['role' => 'coach']);
    $student = \App\Models\User::factory()->create(['role' => 'student']);

    $team = \App\Models\Team::create([
        'coach_id' => $coach->id,
        'name' => 'Leadership Team',
        'join_code' => 'LMN54321',
    ]);

    $team->members()->attach($coach->id, ['role' => 'manager']);
    $team->members()->attach($student->id, ['role' => 'student']);

    $response = $this->actingAs($coach)->post(route('teams.members.role', [$team, $student]), [
        'role' => 'assistant_manager',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('team_user', [
        'team_id' => $team->id,
        'user_id' => $student->id,
        'role' => 'assistant_manager',
    ]);
});

test('team manager can assign a member a volleyball position', function () {
    $coach = \App\Models\User::factory()->create(['role' => 'coach']);
    $manager = \App\Models\User::factory()->create(['role' => 'student']);
    $player = \App\Models\User::factory()->create(['role' => 'student']);

    $team = \App\Models\Team::create([
        'coach_id' => $coach->id,
        'name' => 'Position Team',
        'join_code' => 'POS54321',
    ]);

    $team->members()->attach($coach->id, ['role' => 'manager']);
    $team->members()->attach($manager->id, ['role' => 'manager']);
    $team->members()->attach($player->id, ['role' => 'student']);

    $response = $this->actingAs($manager)->post(route('teams.members.position', [$team, $player]), [
        'position' => 'MB',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('team_user', [
        'team_id' => $team->id,
        'user_id' => $player->id,
        'role' => 'student',
        'position' => 'MB',
    ]);
});

test('team manager can assign player attendance', function () {
    $coach = \App\Models\User::factory()->create(['role' => 'coach']);
    $player = \App\Models\User::factory()->create(['role' => 'student']);

    $team = \App\Models\Team::create([
        'coach_id' => $coach->id,
        'name' => 'Attendance Team',
        'join_code' => 'ATT54321',
    ]);

    $team->members()->attach($coach->id, ['role' => 'manager']);
    $team->members()->attach($player->id, ['role' => 'student']);

    $response = $this->actingAs($coach)->post(route('teams.members.attendance', [$team, $player]), [
        'attendance' => 'substitute',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('team_user', [
        'team_id' => $team->id,
        'user_id' => $player->id,
        'attendance' => 'substitute',
    ]);
});

test('updating attendance preserves the player position', function () {
    $coach = \App\Models\User::factory()->create(['role' => 'coach']);
    $player = \App\Models\User::factory()->create(['role' => 'student']);

    $team = \App\Models\Team::create([
        'coach_id' => $coach->id,
        'name' => 'Attendance Preservation Team',
        'join_code' => 'ATTPRES1',
    ]);

    $team->members()->attach($coach->id, ['role' => 'manager']);
    $team->members()->attach($player->id, [
        'role' => 'student',
        'position' => 'MB',
        'attendance' => 'present',
    ]);

    $this->actingAs($coach)->post(route('teams.members.attendance', [$team, $player]), [
        'attendance' => 'absent',
    ])->assertRedirect();

    $this->assertDatabaseHas('team_user', [
        'team_id' => $team->id,
        'user_id' => $player->id,
        'position' => 'MB',
        'attendance' => 'absent',
    ]);
});

test('team manager cannot promote a member to manager', function () {
    $coach = \App\Models\User::factory()->create(['role' => 'coach']);
    $manager = \App\Models\User::factory()->create(['role' => 'student']);
    $player = \App\Models\User::factory()->create(['role' => 'student']);

    $team = \App\Models\Team::create([
        'coach_id' => $coach->id,
        'name' => 'Manager Permissions Team',
        'join_code' => 'MGRPERM1',
    ]);

    $team->members()->attach($coach->id, ['role' => 'manager']);
    $team->members()->attach($manager->id, ['role' => 'manager']);
    $team->members()->attach($player->id, ['role' => 'student']);

    $this->actingAs($manager)->post(route('teams.members.role', [$team, $player]), [
        'role' => 'manager',
    ])->assertForbidden();

    $this->assertDatabaseHas('team_user', [
        'team_id' => $team->id,
        'user_id' => $player->id,
        'role' => 'student',
    ]);
});

test('team manager cannot assign manager role to an assistant manager', function () {
    $coach = \App\Models\User::factory()->create(['role' => 'coach']);
    $manager = \App\Models\User::factory()->create(['role' => 'student']);
    $assistantManager = \App\Models\User::factory()->create(['role' => 'student']);

    $team = \App\Models\Team::create([
        'coach_id' => $coach->id,
        'name' => 'Manager Role Boundary Team',
        'join_code' => 'MGRBOUND',
    ]);

    $team->members()->attach($coach->id, ['role' => 'manager']);
    $team->members()->attach($manager->id, ['role' => 'manager']);
    $team->members()->attach($assistantManager->id, ['role' => 'assistant_manager']);

    $this->actingAs($manager)->post(route('teams.members.role', [$team, $assistantManager]), [
        'role' => 'manager',
    ])->assertForbidden();

    $this->assertDatabaseHas('team_user', [
        'team_id' => $team->id,
        'user_id' => $assistantManager->id,
        'role' => 'assistant_manager',
    ]);
});

test('assistant manager can update student roster data but not roles', function () {
    $coach = \App\Models\User::factory()->create(['role' => 'coach']);
    $assistantManager = \App\Models\User::factory()->create(['role' => 'student']);
    $player = \App\Models\User::factory()->create(['role' => 'student']);

    $team = \App\Models\Team::create([
        'coach_id' => $coach->id,
        'name' => 'Assistant Permissions Team',
        'join_code' => 'ASSTPERM',
    ]);

    $team->members()->attach($coach->id, ['role' => 'manager']);
    $team->members()->attach($assistantManager->id, ['role' => 'assistant_manager']);
    $team->members()->attach($player->id, ['role' => 'student']);

    $this->actingAs($assistantManager)->post(route('teams.members.position', [$team, $player]), [
        'position' => 'L',
    ])->assertRedirect();

    $this->actingAs($assistantManager)->post(route('teams.members.attendance', [$team, $player]), [
        'attendance' => 'absent',
    ])->assertRedirect();

    $this->actingAs($assistantManager)->post(route('teams.members.role', [$team, $player]), [
        'role' => 'assistant_manager',
    ])->assertForbidden();

    $this->assertDatabaseHas('team_user', [
        'team_id' => $team->id,
        'user_id' => $player->id,
        'role' => 'student',
        'position' => 'L',
        'attendance' => 'absent',
    ]);
});

test('team manager can assign the expanded volleyball positions', function (string $position) {
    $coach = \App\Models\User::factory()->create(['role' => 'coach']);
    $player = \App\Models\User::factory()->create(['role' => 'student']);

    $team = \App\Models\Team::create([
        'coach_id' => $coach->id,
        'name' => 'Expanded Positions Team',
        'join_code' => fake()->unique()->regexify('[A-Z0-9]{8}'),
    ]);

    $team->members()->attach($coach->id, ['role' => 'manager']);
    $team->members()->attach($player->id, ['role' => 'student']);

    $this->actingAs($coach)->post(route('teams.members.position', [$team, $player]), [
        'position' => $position,
    ])->assertRedirect();

    $this->assertDatabaseHas('team_user', [
        'team_id' => $team->id,
        'user_id' => $player->id,
        'position' => $position,
    ]);
})->with(['S', 'RS', 'L']);
