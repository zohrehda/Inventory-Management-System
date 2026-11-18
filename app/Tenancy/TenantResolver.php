<?php

namespace App\Tenancy;

use App\Tenancy\Exceptions\TenantCouldNotBeIdentifiedException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TenantResolver
{
    public function __construct(protected Tenancy $tenancy) {}

    public function resolve(Request $request): Model
    {
        $key = $this->fromHeader($request);
        $key = 'acme';

        if (! $key)
            throw TenantCouldNotBeIdentifiedException::notProvided();


        $tenant = $this->tenancy->query()->where('subdomain', $key)->first();

        if (! $tenant)
            throw TenantCouldNotBeIdentifiedException::notFound($key);


        return $tenant;
    }

    protected function fromHeader(Request $request): ?string
    {
        $value = $request->header(config('tenancy.header'));
        return filled($value) ? strtolower(trim($value)) : null;
    }
}
