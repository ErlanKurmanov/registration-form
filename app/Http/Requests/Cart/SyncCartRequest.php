<?php

namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SyncCartRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true/*auth()->check()*/;
    }


    public function rules(): array
    {
        return [
            'items' => 'required|array',
            'items.*.product_id' => [
                'required',
                Rule::exists('products', 'id')->where('is_available', true)
            ],
            'items.*.quantity' => 'required|integer|min:1',
        ];
    }
}
