<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth; // Для проверки аутентификации

class PlaceOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Заказ могут оформлять только авторизованные пользователи.
     */
    public function authorize(): bool
    {
        return Auth::check(); // Проверяем, авторизован ли пользователь
    }


    public function rules(): array
    {
        return [
            'shipping_address' => 'required|string|max:500',
        //            'billing_address' => 'nullable|string|max:500',
        //            'payment_method' => 'required|in:credit_card,paypal,cash_on_delivery',
            'comment' => 'nullable|string|max:1000',
        ];
    }
}
