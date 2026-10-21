<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolePermissionSeeder extends Seeder {
    public function run(){
        $permissions = [
            ['name'=>'create-own-post', 'display_name'=>'Create & edit own posts', 'description'=>'Can write new posts and edit or delete their own posts.', 'group'=>'Posts'],
            ['name'=>'manage-all-posts', 'display_name'=>'Manage all posts', 'description'=>'Can view, edit and delete any post (admin panel > All Posts).', 'group'=>'Posts'],
            ['name'=>'write-comments', 'display_name'=>'Write comments', 'description'=>'Can comment on posts.', 'group'=>'Comments'],
            ['name'=>'delete-any-comment', 'display_name'=>'Delete any comment', 'description'=>'Can delete comments written by other users.', 'group'=>'Comments'],
            ['name'=>'manage-categories', 'display_name'=>'Manage categories', 'description'=>'Can create and edit categories.', 'group'=>'Admin panel'],
            ['name'=>'manage-users', 'display_name'=>'Manage users', 'description'=>'Can view and manage users.', 'group'=>'Admin panel'],
            ['name'=>'manage-roles', 'display_name'=>'Manage roles', 'description'=>'Can create and edit roles.', 'group'=>'Admin panel'],
        ];

        foreach($permissions as $p) {
            Permission::firstOrCreate(['name' => $p['name']], $p);
        }

        $admin = Role::firstOrCreate(['slug'=>'admin'], ['name'=>'Admin','description'=>'Full access to everything. This role cannot be changed.','is_system'=>true]);
        $admin->permissions()->sync(Permission::all());

        $user = Role::firstOrCreate(['slug'=>'user'], ['name'=>'User','description'=>'Default role for registered users.','is_system'=>true]);
        $user->permissions()->sync(Permission::whereIn('name',['create-own-post','write-comments'])->get());
    }
}