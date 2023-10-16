<?php

use Inertia\Testing\AssertableInertia as Assert;
use Modules\User\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->loggedRequest = $this->actingAs($this->user);

    $this->role = Role::create(['name' => 'root', 'guard_name' => 'user']);
});

test('role list can be rendered', function () {
    $response = $this->loggedRequest->get('/acl-role');

    $response->assertStatus(200);

    $response->assertInertia(
        fn (Assert $page) => $page
            ->component('AclRole/RoleIndex')
            ->has(
                'roles.data',
                1,
                fn (Assert $page) => $page
                    ->where('id', $this->role->id)
                    ->where('name', $this->role->name)
                    ->where('guard_name', $this->role->guard_name)
            )
    );
});

test('role can be created', function () {
    $response = $this->loggedRequest->post('/acl-role', [
        'name' => 'z Role Name', // z to be the last, because the list is ordered by name
        'guard_name' => 'user',
    ]);

    $response->assertRedirect('/acl-role');

    $redirectResponse = $this->loggedRequest->get('/acl-role');
    $redirectResponse->assertInertia(
        fn (Assert $page) => $page
            ->component('AclRole/RoleIndex')
            ->has(
                'roles.data',
                2
            )
            ->has(
                'roles.data.1',
                fn (Assert $page) => $page
                    ->where('id', 2)
                    ->where('name', 'z Role Name')
                    ->where('guard_name', 'user')
            )
    );
});

test('role edit can be rendered', function () {
    $response = $this->loggedRequest->get('/acl-role/' . $this->role->id . '/edit');

    $response->assertStatus(200);

    $response->assertInertia(
        fn (Assert $page) => $page
            ->component('AclRole/RoleForm')
            ->has(
                'role',
                fn (Assert $page) => $page
                    ->where('id', $this->role->id)
                    ->where('name', $this->role->name)
                    ->where('guard_name', $this->role->guard_name)
                    ->etc()
            )
    );
});

test('role can be updated', function () {
    $response = $this->loggedRequest->put('/acl-role/' . $this->role->id, [
        'name' => 'z Role Name',
    ]);

    $response->assertRedirect('/acl-role');

    $redirectResponse = $this->loggedRequest->get('/acl-role');
    $redirectResponse->assertInertia(
        fn (Assert $page) => $page
            ->component('AclRole/RoleIndex')
            ->has(
                'roles.data',
                1,
                fn (Assert $page) => $page
                    ->where('id', $this->role->id)
                    ->where('name', 'z Role Name')
                    ->where('guard_name', $this->role->guard_name)
            )
    );
});

test('role can be deleted', function () {
    $response = $this->loggedRequest->delete('/acl-role/' . $this->role->id);

    $response->assertRedirect('/acl-role');

    $this->assertCount(0, Role::all());
});