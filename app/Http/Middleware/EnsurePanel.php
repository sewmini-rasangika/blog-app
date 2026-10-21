<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\Panel;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;


class EnsurePanel
{
    public function handle(Request $request, Closure $next): Response
    {
        $route = $request->route();
        $current = (string) $route->parameter('panel');

        $route->forgetParameter('panel');

        $user = $request->user();

        if ($user && $current !== Panel::for($user)) {
            $known = $current === config('admin.path')
                || $current === config('admin.user_path')
                || User::where('slug', $current)->exists();

            if (! $known || ! $request->isMethodSafe()) {
                abort(404);
            }

            $segments = $request->segments();
            $segments[0] = Panel::for($user);

            return redirect()->to(url(implode('/', $segments)) . ($request->getQueryString() ? '?' . $request->getQueryString() : ''));
        }

        return $next($request);
    }
}
