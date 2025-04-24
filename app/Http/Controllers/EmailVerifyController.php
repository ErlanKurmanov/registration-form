<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;

class EmailVerifyController extends Controller
{
    public function verify(Request $request, int $id, $hash)
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json(['message' => 'Пользователь не найден.'], 404);
        }

        // 2. Проверить, не верифицирован ли уже email
        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email уже подтвержден.'], 400);
        }

        // 3. Проверить хеш (важно для безопасности)
        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->json(['message' => 'Недействительная ссылка верификации (неверный хеш).'], 400);
        }

        // 4. Пометить email как верифицированный
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return response()->json(['message' => 'Email успешно подтвержден!']);
    }
}
