<?php

use App\Tenancy\Models\Tenant;

return [
    'header' => env('TENANCY_HEADER', 'X-Tenant'),
    'tenant_model' => Tenant::class
];
