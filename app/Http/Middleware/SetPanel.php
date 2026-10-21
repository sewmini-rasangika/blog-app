<?php

namespace App\Http\Middleware;

use App\Support\Panel;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;


class SetPanel
{
    public function handle(Request $request, Closure $next): Response
    {
        URL::defaults(['panel' => Panel::for($request->user())]);

        return $next($request);
    }
}
