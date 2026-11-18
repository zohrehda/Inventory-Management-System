<?php

namespace App\Tenancy\Middleware;

use App\Tenancy\Tenancy;
use App\Tenancy\TenantResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InitializeTenancy
{
    public function __construct(
        protected Tenancy $tenancy,
        protected TenantResolver $resolver,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $this->tenancy->initialize($this->resolver->resolve($request));

        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        // Prevents tenant leakage with long-lived workers (Octane etc.).
        $this->tenancy->end();
    }
}