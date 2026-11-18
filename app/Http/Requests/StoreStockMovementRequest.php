<?php

namespace App\Http\Requests;

use App\Enums\StockMovementType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class StoreStockMovementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $tenant = tenant();
        $inTenant = fn(string $table) => Rule::exists($table, 'id')->where('tenant_id', $tenant->id);

        return [
            'type' => ['required', Rule::enum(StockMovementType::class)],
            'product_id' => ['required', 'integer', $inTenant('products')],
            'warehouse_id' => ['required', 'integer', $inTenant('warehouses')],
            'to_warehouse_id' => [
                'required_if:type,' . StockMovementType::TRANSFER->value,
                'integer',
                'different:warehouse_id',
                $inTenant('warehouses'),
            ],
            'quantity' => ['required', 'integer', 'min:1', 'max:1000000000'],
            'reference' => ['nullable', 'string', 'max:255'],
        ];
    }
}
