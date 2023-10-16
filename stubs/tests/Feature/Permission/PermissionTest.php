<?php

use Inertia\Testing\AssertableInertia as Assert;
use Modules\User\Models\User;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->loggedRequest = $this->actingAs($this->user);

    $this->permission = Permission::create(['name' => 'first']);
});

test('permission list can be rendered', function () {
    $response = $this->loggedRequest->get('/acl-permission');

    $response->assertStatus(200);

    $response->assertInertia(
        fn (Assert $page) => $page
            ->component('AclPermission/PermissionIndex')
            ->has(
                'permissions.data',
                1,
                fn (Assert $page) => $page
                    ->where('id', $this->permission->id)
                    ->where('name', $this->permission->name)
                    ->where('guard', null)
            )
    );
});

test('permission can be created', function () {
    $response = $this->loggedRequest->post('/acl-permission', [
        'name' => 'z Permission Name', // z to be the last, because the list is ordered by name
    ]);

    $response->assertRedirect('/acl-permission');

    $redirectResponse = $this->loggedRequest->get('/acl-permission');
    $redirectResponse->assertInertia(
        fn (Assert $page) => $page
            ->component('AclPermission/PermissionIndex')
            ->has(
                'permissions.data',
                2
            )
            ->has(
                'permissions.data.1',
                fn (Assert $page) => $page
                    ->where('id', 2)
                    ->where('name', 'z Permission Name')
                    ->where('guard', null)
            )
    );
});

test('permission edit can be rendered', function () {
    $response = $this->loggedRequest->get('/acl-permission/' . $this->permission->id . '/edit');

    $response->assertStatus(200);

    $response->assertInertia(
        fn (Assert $page) => $page
            ->component('AclPermission/PermissionForm')
            ->has(
                'permission',
                fn (Assert $page) => $page
                    ->where('id', $this->permission->id)
                    ->where('name', $this->permission->name)
                    ->where('guard_name', $this->permission->guard_name)
                    ->where('created_at', $this->permission->created_at->toISOString())
                    ->where('updated_at', $this->permission->updated_at->toISOString())
            )
    );
});

test('permission can be updated', function () {
    $response = $this->loggedRequest->put('/acl-permission/' . $this->permission->id, [
        'name' => 'z Permission Name',
    ]);

    $response->assertRedirect('/acl-permission');

    $redirectResponse = $this->loggedRequest->get('/acl-permission');
    $redirectResponse->assertInertia(
        fn (Assert $page) => $page
            ->component('AclPermission/PermissionIndex')
            ->has(
                'permissions.data',
                1,
                fn (Assert $page) => $page
                    ->where('id', $this->permission->id)
                    ->where('name', 'z Permission Name')
                    ->where('guard', null)
            )
    );
});

test('permission can be deleted', function () {
    $response = $this->loggedRequest->delete('/acl-permission/' . $this->permission->id);

    $response->assertRedirect('/acl-permission');

    $this->assertCount(0, Permission::all());
});