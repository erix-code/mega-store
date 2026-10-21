<?php

namespace App\Infrastructure\Http\Requests\Sales;

use App\Infrastructure\Persistence\Eloquent\Catalog\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where('is_active', true),
            ],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:1000'],
            'customer_name' => ['nullable', 'string', 'max:150'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'address_id' => [
                'nullable',
                'integer',
                Rule::exists('addresses', 'id')->where('user_id', $this->user()->id),
            ],
            'shipping_address' => ['required_without:address_id', 'nullable', 'array'],
            'shipping_address.line1' => ['required_with:shipping_address', 'string', 'max:255'],
            'shipping_address.city' => ['required_with:shipping_address', 'string', 'max:100'],
            'shipping_address.state' => ['required_with:shipping_address', 'string', 'max:100'],
            'shipping_address.postal_code' => ['required_with:shipping_address', 'string', 'max:20'],
            'shipping_address.country' => ['nullable', 'string', 'size:2'],
        ];
    }

    /**
     * @return array<callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->hasAny(['items'])) {
                    return;
                }

                foreach ($this->input('items', []) as $index => $item) {
                    $product = Product::find($item['product_id'] ?? null);

                    if ($product === null) {
                        continue;
                    }

                    if (($item['quantity'] ?? 0) > $product->stock) {
                        $validator->errors()->add(
                            "items.{$index}.quantity",
                            "Not enough stock for {$product->name}."
                        );
                    }
                }
            },
        ];
    }
}
