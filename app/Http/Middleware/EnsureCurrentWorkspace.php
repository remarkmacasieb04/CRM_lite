<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCurrentWorkspace
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        $workspace = $user->resolveCurrentWorkspace();

        if ($workspace === null) {
            abort(Response::HTTP_FORBIDDEN, 'No workspace is available for this account.');
        }

        $request->attributes->set('currentWorkspace', $workspace);
        $request->attributes->set('currentWorkspaceRole', $user->current_workspace_role?->value);

        return $next($request);
    }
}
