<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{

    public function authorize(): bool
    {
        // e.g. only allow if user owns the product or is admin
//        return auth()->check();

        return true;
    }

    public function rules(): array
    {

        return [
            'category_id'  => 'sometimes|integer|exists:categories,id',
            'name'         => 'sometimes|string|max:255|',
            'description'  => 'sometimes|string',
            'price'        => 'sometimes|numeric|min:0',
            'is_available' => 'sometimes|boolean',
        ];
    }
}
