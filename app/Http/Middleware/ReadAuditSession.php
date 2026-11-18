<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\StartSession;

class ReadAuditSession
{
    // A background click must not age flash messages, replace a login cookie, or
    // overwrite session changes made by a form submission running at the same time.
    public function handle(Request $request, Closure $next)
    {
        $session = app(StartSession::class)->getSession($request);
        $session->setRequestOnHandler($request);
        $session->start();
        $request->setLaravelSession($session);

        return $next($request);
    }
}
