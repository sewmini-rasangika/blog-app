<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use App\Support\Permissions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Support\Activity;

class PermissionController extends Controller
{
    public function create()
    {
        return view('admin.permissions.create', ['groupNames' => array_keys(Permissions::groups())]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        Permission::create($data + ['is_system' => false]);

        return redirect()->route('roles.index')
            ->with('success', "Permission \"{$data['name']}\" added. Now tick it for a role (Roles > Edit).");
    }

    public function edit(Permission $permission)
    {
        return view('admin.permissions.edit', [
            'permission' => $permission,
            'groupNames' => array_keys(Permissions::groups()),
        ]);
    }

    public function update(Request $request, Permission $permission)
    {
        $data = $this->validated($request, $permission);

      
        if ($permission->is_system) {
            unset($data['name']);
        }

        $old = $permission->name;

        DB::transaction(function () use ($permission, $data, $old) {
            $permission->update($data);

          
            if (isset($data['name']) && $data['name'] !== $old) {
                $this->replaceInRoles($old, $data['name']);
            }
        });

        return redirect()->route('roles.index')->with('success', 'Permission updated.');
    }

    public function destroy(Permission $permission)
    {
        if ($permission->is_system) {
            return back()->with('error', 'This permission is used by the app itself, so it cannot be deleted.');
        }

        DB::transaction(function () use ($permission) {
            $this->replaceInRoles($permission->name, null);   
            $permission->delete();
        });

        return redirect()->route('roles.index')->with('success', 'Permission deleted and removed from all roles.');
    }

    
    private function replaceInRoles(string $old, ?string $new): void
    {
        foreach (Role::all() as $role) {
            $list = $role->permissions ?? [];

            if (! in_array($old, $list, true)) {
                continue;
            }

            $list = array_map(fn ($k) => $k === $old ? $new : $k, $list);

            $role->permissions = array_values(array_unique(array_filter($list)));
            $role->save();
        }
    }

    private function validated(Request $request, ?Permission $permission = null): array
    {
        $data = $request->validate([
            'name' => [
                'required', 'string', 'max:60',
                'regex:/^[a-z][a-z0-9_]*(\.[a-z][a-z0-9_]*)+$/',  
                Rule::unique('permissions', 'name')->ignore($permission?->id),
            ],
            'display_name' => ['required', 'string', 'max:120'],
            'group' => ['required', 'string', 'max:40'],
            'description' => ['nullable', 'string', 'max:255'],
        ], [
            'name.regex' => 'Use the format  module.action  (small letters), e.g. reports.view',
        ]);

        $data['is_sensitive'] = $request->boolean('is_sensitive');

        return $data;
    }
}
