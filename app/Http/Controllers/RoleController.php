<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{

    public function index()
    {
        // 1. Fetch paginated roles
        $roles = Role::where('name', '!=', 'superadmin')->latest()->paginate(10);

        // 2. Pass the 'roles' variable to the view
        return Inertia::render('Roles/Index', [
            'roles' => $roles
        ]);
    }

    public function create()
    {
        $allPermissions = Permission::all();

        // Grouping specifically into two categories
        $groups = [
            'User Management' => $allPermissions->filter(function ($p) {
                return str_contains($p->name, 'user');
            })->values(),
            'Role Management' => $allPermissions->filter(function ($p) {
                return str_contains($p->name, 'role');
            })->values(),
        ];

        return Inertia::render('Roles/Create', [
            'permissionGroups' => $groups
        ]);
    }

    public function store(Request $request)
    {
        // 1. Validate the request
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'exists:permissions,id',
        ], [
            // Custom error messages
            'permissions.required' => 'Please select at least one permission for this role.',
            'name.unique' => 'This role name already exists.'
        ]);

        // 2. Create the Role
        // Note: 'web' is the default guard.
        $role = Role::create([
            'name' => $request->name,
            'guard_name' => 'web'
        ]);

        // 3. Sync Permissions
        // Spatie's syncPermissions accepts an array of IDs, names, or models.
        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        // 4. Redirect with flash message
        return redirect()
            ->route('roles.index')
            ->with('success', 'Role "' . strtoupper($role->name) . '" created and permissions assigned successfully.');
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        // 1. Find the role
        $role = Role::findOrFail($id);

        // 2. Fetch and Group Permissions (Exactly like the create page)
        $allPermissions = Permission::all();
        $groups = [
            'User Management' => $allPermissions->filter(fn($p) => str_contains($p->name, 'user'))->values(),
            'Role Management' => $allPermissions->filter(fn($p) => str_contains($p->name, 'role'))->values(),
        ];

        // 3. Get currently assigned permission IDs
        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return Inertia::render('Roles/Edit', [
            'role' => $role,
            'permissionGroups' => $groups,
            'rolePermissions' => $rolePermissions,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            // Allow same name for this specific role, but unique against others
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissions' => 'required|array|min:1',
        ]);

        $role->update(['name' => $request->name]);

        // This replaces old permissions with new ones automatically
        $role->syncPermissions($request->permissions);

        return redirect()
            ->route('roles.index')
            ->with('success', 'Role updated successfully');
    }

    public function destroy(string $id)
    {
        //
    }
}
