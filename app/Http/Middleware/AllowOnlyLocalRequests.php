<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AllowOnlyLocalRequests
{
    /** @var list<string> */
    private array $allowedIps = [
        '174.138.10.78',
        '94.227.146.156',
        '127.0.0.1',
        '::1',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (! in_array($request->ip(), $this->allowedIps)) {
            abort(403);
        }

        return $next($request);
    }
}
