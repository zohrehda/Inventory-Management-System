<?php

namespace App\Tenancy;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Tenancy
{
    protected ?Model $tenant = null;

    public function model(): string
    {
        return config('tenancy.tenant_model');
    }

    public function query(): Builder
    {
        return (new ($this->model()))->newQuery();
    }

    public function initialize(Model $tenant): void
    {
        $this->tenant = $tenant;
    }
    
    public function end(): void
    {
        $this->tenant = null;
    }

    public function initialized(): bool
    {
        return $this->tenant !== null;
    }

    public function tenant(): ?Model
    {
        return $this->tenant;
    }

    public function id(): int|string|null
    {
        return $this->tenant?->getKey();
    }


    public function run(Model $tenant, Closure $callback): mixed
    {
        $previous = $this->tenant;
        $this->initialize($tenant);

        try {
            return $callback($tenant);
        } finally {
            $previous ? $this->initialize($previous) : $this->end();
        }
    }
}