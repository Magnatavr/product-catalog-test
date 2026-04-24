<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * Контроллер аутентификации (выдача токена через Sanctum)
 */
class AuthController extends Controller
{
    /**
     * POST /api/login
     * Принимает email и пароль, проверяет учётные данные и выдаёт токен для админ-панели
     *
     * @param Request $request (содержит поля email и password)
     * @return JsonResponse (возвращает токен и данные пользователя или ошибку 401)
     */
    public function login(Request $request): JsonResponse
    {
        // Валидация входящих данных (ручная, без отдельного FormRequest)
        $request->validate([
            'email' => 'required|email',      // обязательный и валидный email
            'password' => 'required',          // обязательный пароль
        ]);

        // Ищем пользователя по email (первого совпавшего)
        $user = User::where('email', $request->email)->first();

        // Проверяем: существует ли пользователь и совпадает ли пароль
        // Hash::check сравнивает открытый пароль с хешем из базы
        if (!$user || !Hash::check($request->password, $user->password)) {
            // Если не совпадает – возвращаем 401 Unauthorized
            return response()->json(['message' => 'Неверный email или пароль'], 401);
        }

        // Генерация токена через Sanctum
        // Создаём токен с именем 'auth_token' (можно использовать другое имя)
        $token = $user->createToken('auth_token')->plainTextToken;

        // Возвращаем токен и данные пользователя (без пароля, так как модель скрывает поле password)
        return response()->json(['token' => $token, 'user' => $user]);
    }
}
