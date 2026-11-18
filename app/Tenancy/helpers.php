<?php

use App\Tenancy\Tenancy;

if (! function_exists('tenancy')) {
    function tenancy(): Tenancy
    {
        return app(Tenancy::class);
    }
}

if (! function_exists('tenant')) {
    function tenant(): mixed
    {
        return app(Tenancy::class)->tenant();
    }
}
