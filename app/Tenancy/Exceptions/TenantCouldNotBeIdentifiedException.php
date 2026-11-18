<?php

namespace App\Tenancy\Exceptions;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TenantCouldNotBeIdentifiedException extends NotFoundHttpException
{
    public static function notProvided(): self
    {
        return new self(trans('messages.tenant_not_provided'));
    }

    public static function notFound(string $key): self
    {
        return new self(trans('messages.tenant_not_found', ['key' => $key]));
    }
}
