<?php

namespace App\Http\Controllers;

use App\Mail\ConfirmAccountDeletion;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;

// Событие для отправки письма верификации
// Создадим этот Mailable позже

class AuthController extends Controller
{

    /**
     * Регистрация нового пользователя.
     */
    public function register(Request $request)
    {
//        return response()->json('register');

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422); // 422 Unprocessable Entity
        }

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
        ]);

//         Sends email
        event(new Registered($user));

        return response()->json([
            'message' => 'Пользователь успешно зарегистрирован. Пожалуйста, проверьте свою почту для подтверждения аккаунта.',
            'user' => $user // Можно вернуть данные пользователя (без пароля)
        ], 201); // 201 Created
    }


    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);


        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $credentials = $request->only('email', 'password');

        if (! $token = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'Неверные учетные данные'], 401);
        }


        $user = auth('api')->user();
        if (!$user->hasVerifiedEmail()) {
            auth('api')->logout(); // Разлогиниваем, если почта не подтверждена
            return response()->json(['error' => 'Пожалуйста, подтвердите ваш email адрес.'], 403); // 403 Forbidden
        }


        return $this->respondWithToken($token);
    }


    public function me()
    {
        // auth('api')->user() вернет текущего пользователя по токену
        return response()->json(auth('api')->user());
    }


    public function logout()
    {
        auth('api')->logout();

        return response()->json(['message' => 'Успешный выход']);
    }


    public function refresh()
    {
        // Обновляем токен текущего пользователя
        return $this->respondWithToken(auth('api')->refresh());
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            // Время жизни токена в секундах
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }

    /**
     * Запрос на удаление аккаунта.
     */
    public function requestAccountDeletion(Request $request)
    {
        $user = auth('api')->user();

        // Генерируем подписанный URL для подтверждения удаления
        // URL будет действителен 1 час (3600 секунд)
//        $verificationUrl = URL::temporarySignedRoute(
//            'api.auth.confirm-deletion', // Имя маршрута (определим позже)
//            now()->addHour(),
//            ['user' => $user->id]
//        );

        // Отправляем письмо с ссылкой для подтверждения
        // TODO: Создать Mailable 'ConfirmAccountDeletion'
//        Mail::to($user->email)->send(new ConfirmAccountDeletion($user, $verificationUrl));

        return response()->json(['message' => 'Письмо с подтверждением удаления отправлено на вашу почту.']);
    }

    /**
     * Подтверждение и удаление аккаунта.
     * Этот метод будет вызываться при переходе по подписанной ссылке.
     * Laravel автоматически проверит подпись URL.
     */
    public function confirmAccountDeletion(Request $request, User $user)
    {

        if (!$request->hasValidSignature()) {
            abort(401, 'Недействительная или просроченная ссылка.');
        }


        $userEmail = $user->email;
        $user->delete();
        return response()->json(['message' => "Аккаунт для {$userEmail} успешно удален."]);
    }



}
