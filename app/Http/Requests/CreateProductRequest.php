<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Set to true if you don't have specific authorization logic here yet
        // Or add logic like: return auth()->check() && auth()->user()->can('create-product');
        return true;
    }

    public function rules(): array
    {
        
        return [
            
            'category_id'   => ['required', 'exists:categories,id'],
            'name'          => ['required', 'string', 'max:255'],
            'description'   => ['nullable', 'string'],
            'price'         => [
                'required',
                'numeric',
                'decimal:2',
                'min:0',
            ],
            'is_available'  => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        
        return [
            'category_id.required' => 'The product must belong to a category.',
            'category_id.exists' => 'The selected category is invalid.',
            'name.required' => 'Please enter the product name.',
            'name.string' => 'The product name must be text.',
            'name.max' => 'The product name cannot be longer than :max characters.',
            'description.string' => 'The description must be text.',
            'price.required' => 'Please enter the product price.',
            'price.decimal' => 'The price must be a number with up to 2 decimal places.',
            'price.min' => 'The price cannot be negative.',
            'is_available.boolean' => 'The availability status must be true or false.',
        ];
    }
}
