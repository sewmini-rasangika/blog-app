<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Support\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with('profile')
            ->withCount([
                'posts',
                'posts as pending_count' => fn ($q) => $q->where('status', 'pending'),
            ])
            ->when($request->filled('q'), fn ($q) => $q->where(function ($w) use ($request) {
                $w->where('name', 'like', '%' . $request->input('q') . '%')
                  ->orWhere('email', 'like', '%' . $request->input('q') . '%');
            }))
            ->latest()->paginate(15)->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => $this->assignableRoles(),         
            'roleNames' => Role::pluck('name', 'slug'),   
        ]);
    }

    public function create()
    {
        return view('admin.users.create', ['roles' => $this->assignableRoles()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', Rule::in($this->assignableRoles()->pluck('slug')->all())],
        ]);

        $user = new User([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
        $user->role = $data['role'];  
        $user->save();

        $user->profile()->firstOrCreate([]);

        Activity::log('user.created', "Created user {$user->name}", $user, ['role' => $data['role']]);

        return redirect()->route('users.index')->with('success', 'User created.');
    }


    public function updateRole(Request $request, User $user)
    {
        $actor = auth()->user();

        $data = $request->validate([
            'role' => ['required', Rule::in($this->assignableRoles()->pluck('slug')->all())],
        ]);

        if ($user->id === $actor->id) {
            return back()->with('error', 'cannot edit your role.');
        }


        if ($user->isAdmin() && ! $actor->isAdmin()) {
            abort(403);
        }

        $user->forceFill(['role' => $data['role']])->save();

        Activity::log('user.role_changed', "Changed {$user->name}'s role", $user, ['role' => $data['role']]);

        return back()->with('success', "{$user->name} change role.");
    }

    public function destroy(User $user)
    {

        if ($user->isAdmin() && ! auth()->user()->isAdmin()) {
            abort(403);
        }

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete yourself".');
        }

        if ($user->posts()->withTrashed()->exists()) {
            return back()->with('error', 'Cannot delete a user who has posts.');
        }

        $user->loadMissing('profile');
        if ($user->profile?->avatar) {
            Storage::disk('public')->delete($user->profile->avatar);
        }

        Activity::log('user.deleted', "Deleted user {$user->name}");

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted.');
    }


    private function assignableRoles()
    {
        return Role::orderBy('name')
            ->when(! auth()->user()->isAdmin(), fn ($q) => $q->where('slug', '!=', 'admin'))
            ->get();
    }
}