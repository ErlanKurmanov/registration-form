<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{

    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
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
