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
        $allPermissions = \Spatie\Permission\Models\Permission::all();

        $permissionGroups = [
            'User Management'           => 'user',
            'Role Management'           => 'role',
            'Brands Management'         => 'brand',
            'Product Category'          => 'product category',
            'Product Unit'              => 'product unit',
            'Product Main'              => 'product main',
            'Selling Price'             => 'selling price',
            'Warehouse Management'      => 'warehouse',
            'Warehouse Stock'           => 'warehouse stock',
            'Stock Ledgers'             => 'stock ledgers',
            'Opening Stocks'            => 'opening stocks',
            'Opening Stock Details'     => 'opening stock details',
            'Stock Adjustments'         => 'stock adjustments',
            'Stock Adjustments Detail'  => 'stock adjustments detail',
            'Stock Transfers'           => 'stock transfers',
            'Stock Transfer Detail'     => 'stock transfer detail',
            'Damaged Stocks'            => 'damaged stocks',
            'Damaged Stock Details'     => 'damaged stock details',
            'Purchase Management'       => 'purchase',
            'Purchase Details'          => 'purchase details',
            'Purchase Return'           => 'purchase return',
            'Purchase Return Details'   => 'purchase return details',
            'Purchase Expense'          => 'purchase expense',
            'Customer Management'       => 'customer',
            'Customer Ledgers'          => 'customer ledgers',
            'Sale Management'           => 'sale',
            'Sale Detail Management'    => 'sale detail',
            'Sale Return Management'    => 'sale return',
            'Sale Return Details'    => 'sale return detail',
        ];

        $groups = [];

        foreach ($permissionGroups as $title => $keyword) {
            $groups[$title] = $allPermissions->filter(function ($permission) use ($keyword, $permissionGroups, $title) {
                $name = strtolower($permission->name);

                // 1. Check if the keyword exists in the permission name
                if (stripos($name, $keyword) === false) return false;

                // 2. Prevent overlap (e.g., stop 'opening stocks' from taking 'opening stock details')
                foreach ($permissionGroups as $otherTitle => $otherKeyword) {
                    // If there's a more specific (longer) keyword that also matches, this permission belongs there instead
                    if ($otherTitle !== $title && strlen($otherKeyword) > strlen($keyword)) {
                        if (stripos($name, $otherKeyword) !== false) {
                            return false;
                        }
                    }
                }
                return true;
            })->values();
        }

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

        // 2. Fetch and Group Permissions (identical logic to create())
        $allPermissions = Permission::all();

        $permissionGroups = [
            'User Management'           => 'user',
            'Role Management'           => 'role',
            'Brands Management'         => 'brand',
            'Product Category'          => 'product category',
            'Product Unit'              => 'product unit',
            'Product Main'              => 'product main',
            'Selling Price'             => 'selling price',
            'Warehouse Management'      => 'warehouse',
            'Warehouse Stock'           => 'warehouse stock',
            'Stock Ledgers'             => 'stock ledgers',
            'Opening Stocks'            => 'opening stocks',
            'Opening Stock Details'     => 'opening stock details',
            'Stock Adjustments'         => 'stock adjustments',
            'Stock Adjustments Detail'  => 'stock adjustments detail',
            'Stock Transfers'           => 'stock transfers',
            'Stock Transfer Detail'     => 'stock transfer detail',
            'Damaged Stocks'            => 'damaged stocks',
            'Damaged Stock Details'     => 'damaged stock details',
            'Purchase Management'       => 'purchase',
            'Purchase Details'          => 'purchase details',
            'Purchase Return'           => 'purchase return',
            'Purchase Return Details'   => 'purchase return details',
            'Purchase Expense'          => 'purchase expense',
            'Customer Management'       => 'customer',
            'Customer Ledgers'          => 'customer ledgers',
            'Sale Management'           => 'sale',
            'Sale Detail Management'    => 'sale detail',
            'Sale Return Management'    => 'sale return',
            'Sale Return Details'    => 'sale return detail',
        ];

        $groups = [];

        foreach ($permissionGroups as $title => $keyword) {
            $groups[$title] = $allPermissions->filter(function ($permission) use ($keyword, $permissionGroups, $title) {
                $name = strtolower($permission->name);

                // 1. Check if the keyword exists in the permission name
                if (stripos($name, $keyword) === false) return false;

                // 2. Prevent overlap (e.g., stop 'opening stocks' from taking 'opening stock details')
                foreach ($permissionGroups as $otherTitle => $otherKeyword) {
                    if ($otherTitle !== $title && strlen($otherKeyword) > strlen($keyword)) {
                        if (stripos($name, $otherKeyword) !== false) {
                            return false;
                        }
                    }
                }
                return true;
            })->values();
        }

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
