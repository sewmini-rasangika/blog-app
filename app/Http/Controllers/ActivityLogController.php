<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Support\Activity;

class ActivityLogController extends Controller
{
    private const GROUPS = ['auth', 'post', 'comment', 'user', 'role', 'category', 'tag', 'settings'];

    public function index(Request $request)
    {
        $request->validate([
            'user'  => 'nullable|integer',
            'group' => ['nullable', Rule::in(self::GROUPS)],
            'from'  => 'nullable|date',
            'to'    => 'nullable|date',
            'q'     => 'nullable|string|max:100',
        ]);

        $like = '%' . addcslashes(trim((string) $request->input('q')), '%_\\') . '%';

        $logs = ActivityLog::query()
            ->when($request->filled('user'), fn ($q) => $q->where('user_id', $request->input('user')))
            ->when($request->filled('group'), fn ($q) => $q->where('action', 'like', $request->input('group') . '.%'))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->input('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->input('to')))
            ->when($request->filled('q'), fn ($q) => $q->where(
                fn ($w) => $w->where('description', 'like', $like)
                    ->orWhere('user_name', 'like', $like)
                    ->orWhere('ip_address', 'like', $like)
            ))
            ->latest('id')
            ->paginate(30)
            ->withQueryString();

        return view('admin.activity.index', [
            'logs'   => $logs,
            'groups' => self::GROUPS,
            'users'  => User::orderBy('name')->get(['id', 'name']),
        ]);
    }
}