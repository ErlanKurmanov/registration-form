<?php

// app/Http/Controllers/Api/Admin/AdminUserController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

// Для отправки верификации, если нужно

class AdminUserController extends Controller
{

    public function index()
    {
        // Можно добавить пагинацию для больших списков
        $users = User::paginate(15); // Например, 15 пользователей на страницу
        return response()->json($users);
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8', // Не 'confirmed', админ задает пароль напрямую
            'phone' => 'nullable|string|max:20',
            'is_admin' => 'sometimes|boolean', // Разрешаем админу назначать других админов
            'send_verification' => 'sometimes|boolean' // Опционально: отправить ли верификацию
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $userData = $request->only(['first_name', 'last_name', 'email', 'phone', 'is_admin']);
        $userData['password'] = Hash::make($request->password);

        // Можно добавить верификацию пользователя сразу
        // $userData['email_verified_at'] = now();

        $user = User::create($userData);

        // Опционально: Отправить письмо верификации, если указано
        if ($request->boolean('send_verification')) {
            // $user->sendEmailVerificationNotification(); // Стандартный метод
            event(new Registered($user)); // Или через событие
        } else {
            // Если верификация не нужна, можно пометить email как подтвержденный
            $user->markEmailAsVerified();
        }


        return response()->json([
            'message' => 'Пользователь успешно создан администратором.',
            'user' => $user
        ], 201);
    }

    public function show(User $user)
    {
        return response()->json($user);
    }


    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            // Уникальность email проверяем, игнорируя текущего пользователя
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
            'password' => 'sometimes|nullable|string|min:8', // Новый пароль (если нужно изменить)
            'is_admin' => 'sometimes|boolean', // Админ может менять роль
            'email_verified_at' => 'sometimes|nullable|date', // Админ может вручную подтвердить/снять подтверждение
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $userData = $request->only(['first_name', 'last_name', 'email', 'phone', 'is_admin']);

        // Обновляем пароль, если он был передан
        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        // Обновляем статус верификации, если передан
        if ($request->has('email_verified_at')) {
            // Принимаем null для снятия верификации или дату/время для установки
            $userData['email_verified_at'] = $request->input('email_verified_at') ? now() : null;
        }


        $user->update($userData);

        return response()->json([
            'message' => 'Данные пользователя успешно обновлены администратором.',
            'user' => $user->fresh()
        ]);
    }

    public function destroy(User $user)
    {
        // Админ не может удалить сам себя через этот эндпоинт
        if ($user->id === auth('api')->id()) {
            return response()->json(['error' => 'Администратор не может удалить свой собственный аккаунт через этот интерфейс.'], 403);
        }

        $userEmail = $user->email;
        $user->delete();

        return response()->json(['message' => "Пользователь {$userEmail} успешно удален администратором."]);

    }
}
