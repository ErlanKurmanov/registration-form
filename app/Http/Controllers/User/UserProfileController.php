<?php

// app/Http/Controllers/Api/UserProfileController.php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

// Для правила unique

class UserProfileController extends Controller
{

    public function show()
    {
        $user = auth('api')->user();
        return response()->json($user);
    }


    public function update(Request $request)
    {
        $user = auth('api')->user();

        $validator = Validator::make($request->all(), [
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            // Email можно менять, но это потребует повторной верификации.
            // Для простоты пока запретим или потребуем отдельный процесс.
            // 'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
            'password' => 'sometimes|nullable|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Обновляем только те поля, которые были переданы в запросе
        $userData = $request->only(['first_name', 'last_name', 'phone']);

        // Обновляем пароль, если он был передан и прошел валидацию
        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        $user->update($userData);

        return response()->json([
            'message' => 'Профиль успешно обновлен.',
            'user' => $user->fresh() // Возвращаем обновленные данные
        ]);
    }


}
