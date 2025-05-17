<?php

// app/Http/Middleware/AdminMiddleware.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // проверка на админа is_admin = true
        if (Auth::guard('api')->check() && Auth::guard('api')->user()->is_admin) {
            return $next($request); // Разрешаем доступ
        }

        // Если нет, возвращаем ошибку 403 Forbidden
        return response()->json(['error' => 'Доступ запрещен. Требуются права администратора.'], 403);
    }
}
