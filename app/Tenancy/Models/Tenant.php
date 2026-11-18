<?php

namespace App\Tenancy\Models;

use App\Tenancy\Database\Factories\TenantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'subdomain'];

    protected static function newFactory(): TenantFactory
    {
        return TenantFactory::new();
    }
}
