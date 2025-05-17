<?php

namespace App\Providers;

use App\Contracts\Repositories\CartRepositoryInterface;
use App\Contracts\Services\CartServiceInterface;
use App\Contracts\Services\FavoriteServiceInterface;
use App\Contracts\Services\OrderServiceInterface;
use App\Contracts\Services\ProductServiceInterface;
use App\Repositories\CartRepository;
use App\Services\CartService;
use App\Services\FavoriteService;
use App\Services\OrderService;
use App\Services\ProductService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ProductServiceInterface::class, ProductService::class);
        $this->app->bind(OrderServiceInterface::class, OrderService::class);
        $this->app->bind(FavoriteServiceInterface::class, FavoriteService::class);
        $this->app->bind(CartServiceInterface::class, CartService::class);
        $this->app->bind(CartRepositoryInterface::class, CartRepository::class);

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
