<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'status' => $this->status,
            'total' => $this->total,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'items' => $this->items->map(
                function ($item) {
                    return [
                    'product_id' => $item->product_id,
                    'name' => $item->product->name,
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                    'options' => $item->options,
                    'subtotal' => $item->price * $item->quantity,
                    ];
                }
            ),
            'shipping_address' => $this->shipping_address,
            'billing_address' => $this->billing_address,
            'payment_method' => $this->payment_method,
            'notes' => $this->notes,
        ];
    }
}
