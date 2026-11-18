<?php

namespace App\Tenancy\Scopes;

use App\Tenancy\Tenancy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $tenancy = tenancy();
        
        if (! $tenancy->initialized()) 
            return;
        

        $builder->where(
            $model->qualifyColumn($model->getTenantColumn()),
            $tenancy->id()
        );
    }
}
