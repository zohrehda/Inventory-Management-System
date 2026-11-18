<?php

namespace App\Tenancy\Concerns;

use App\Tenancy\Scopes\TenantScope;
use App\Tenancy\Tenancy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (Model $model) {
            $tenancy = app(Tenancy::class);

            if ($tenancy->initialized() && ! $model->getAttribute($model->getTenantColumn())) {
                $model->setAttribute($model->getTenantColumn(), $tenancy->id());
            }
        });
    }

    public function getTenantColumn(): string
    {
        return 'tenant_id';
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(app(Tenancy::class)->model(), $this->getTenantColumn());
    }
}