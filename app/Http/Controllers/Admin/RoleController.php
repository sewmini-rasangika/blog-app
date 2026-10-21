<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoleController extends Controller {
    public function index(){
        $roles = Role::withCount(['users','permissions'])->get();
        return view('admin.roles.index', compact('roles'));
    }
    public function create(){
        $groups = Permission::all()->groupBy('group');
        return view('admin.roles.create', compact('groups'));
    }
    public function store(Request $request){
        $request->validate(['name'=>'required|unique:roles,name']);
        $role = Role::create([
            'name'=>$request->name,
            'slug'=>Str::slug($request->name),
            'description'=>$request->description,
        ]);
        $role->permissions()->attach($request->permissions ?? []);
        return redirect()->route('admin.roles.index');
    }
}
