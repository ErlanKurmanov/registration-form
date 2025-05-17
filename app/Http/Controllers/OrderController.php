<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\PlaceOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Cart;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService
    ) {
    }

    /**
     * Получить список заказов пользователя
     */
    public function index(): AnonymousResourceCollection
    {
        $user = Auth::user();
        $orders = Order::with(['items.product', 'user'])
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        return OrderResource::collection($orders);
    }

    /**
     * Создать новый заказ из текущей корзины
     */
    public function store(PlaceOrderRequest $request): JsonResponse|OrderResource
    {
        $user = Auth::user();

        // Получаем корзину пользователя с товарами
        $cart = Cart::with(['items.product'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        if ($cart->items->isEmpty()) {
            return response()->json(['message' => 'Cart is empty'], 400);
        }

        DB::beginTransaction();

        try {
            // Создаем заказ через сервис
            $order = $this->orderService->createOrderFromCart($user, $cart, $request->validated());

            // Очищаем корзину
            $cart->items()->delete();

            DB::commit();

            return (new OrderResource($order->load(['items.product', 'user'])))
                ->response()
                ->setStatusCode(201);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(
                [
                'message' => 'Order creation failed: ' . $e->getMessage()
                ], 400
            );
        }
    }

    /**
     * Просмотр конкретного заказа
     */
    public function show(int $orderId): OrderResource|JsonResponse
    {
        $order = Order::with(['items.product', 'user'])
            ->where('user_id', Auth::id())
            ->where('id', $orderId)
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        return new OrderResource($order);
    }
}
