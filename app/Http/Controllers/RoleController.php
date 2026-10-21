<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\Activity;
use App\Support\Permissions;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index()
    {
        return view('admin.roles.index', [
            'roles' => Role::orderByRaw("slug = 'admin' desc")->orderBy('name')->get(),   
            'counts' => User::selectRaw('role, count(*) as c')->groupBy('role')->pluck('c', 'role'),  
            'groups' => Permissions::groups(),
            'permissions' => Permission::all()->keyBy('name'),
        ]);
    }

    public function create()
    {
        return view('admin.roles.create', ['groups' => Permissions::groups()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $role = Role::create([
            'name' => $data['name'],
            'slug' => $this->uniqueSlug($data['name']),
            'description' => $data['description'] ?? null,
            'permissions' => array_values($data['permissions'] ?? []),
            'is_system' => false,
        ]);

        Activity::log('role.created', "Created role {$role->name}", $role, ['permissions' => $role->permissions]);

        return redirect()->route('roles.index')->with('success', 'Role created.');
    }


    public function edit(Role $role)
    {
        return view('admin.roles.edit', [
            'role' => $role,
            'groups' => Permissions::groups(),
            'readonly' => $role->slug === 'admin',
        ]);
    }

    public function update(Request $request, Role $role)
    {
        if ($role->slug === 'admin') {
            return redirect()->route('roles.index')->with('error', 'The Admin role cannot be changed.');
        }

        $data = $this->validated($request, $role);

       
        $role->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'permissions' => array_values($data['permissions'] ?? []),
        ]);

        Activity::log('role.updated', "Updated role {$role->name}", $role, ['permissions' => $role->permissions]);

        return redirect()->route('roles.index')->with('success', 'Role updated. current effect change.');
    }

    public function destroy(Role $role)
    {
        if ($role->is_system) {
            return back()->with('error', 'cannot delete system role.');
        }

        if (User::where('role', $role->slug)->exists()) {
            return back()->with('error', 'This role has users. previous change own role.');
        }

        Activity::log('role.deleted', "Deleted role {$role->name}");

        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Role deleted.');
    }

    private function validated(Request $request, ?Role $role = null): array
    {
       
        $allowed = Permissions::keys();

        return $request->validate([
            'name' => ['required', 'string', 'max:60', Rule::unique('roles', 'name')->ignore($role?->id)],
            'description' => ['nullable', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => [Rule::in($allowed)],
        ]);
    }

    private function uniqueSlug(string $name): string
    {
        $slug = Str::slug($name) ?: 'role';
        $original = $slug;
        $i = 1;

       
        while ($slug === 'admin' || Role::where('slug', $slug)->exists()) {
            $slug = $original . '-' . $i++;
        }

        return $slug;
    }
}