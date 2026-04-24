<!--
  Компонент: страница входа в админ-панель
  Путь: /login
  Что делает: отправляет запрос на /api/login, получает токен, сохраняет в localStorage и редиректит в админку
  Использует: MainLayout (публичный лэйаут)
-->
<template>
    <MainLayout>
        <!-- Центрируем форму по вертикали и горизонтали -->
        <div class="flex justify-center items-center min-h-[70vh]">
            <!-- Карточка формы входа -->
            <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
                <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Вход в админ-панель</h2>

                <!-- Форма: при сабмите вызываем submit (превентим стандартное поведение) -->
                <form @submit.prevent="submit" class="space-y-4">
                    <!-- Поле email -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" v-model="form.email"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="admin@example.com" />
                    </div>

                    <!-- Поле пароль -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Пароль</label>
                        <input type="password" v-model="form.password"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="••••••••" />
                    </div>

                    <!-- Кнопка отправки -->
                    <button type="submit"
                            class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 transition focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Войти
                    </button>

                    <!-- Сообщение об ошибке (если есть) -->
                    <div v-if="error" class="text-red-600 text-sm text-center mt-2">
                        {{ error }}
                    </div>
                </form>

                <!-- Подсказка с тестовыми данными (удобно для проверки) -->
                <div class="mt-4 text-xs text-gray-500 text-center border-t pt-4">
                    Тестовые данные: admin@example.com / password
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
// Импорты
import MainLayout from '@/Layouts/MainLayout.vue';   // основной лэйаут
import { ref } from 'vue';                           // реактивная переменная
import axios from '@/axios';                         // настроенный axios (базовый URL /api)
import { router } from '@inertiajs/vue3';            // Inertia-роутер для редиректа

// Реактивные данные
const form = ref({ email: '', password: '' });   // данные формы (пустые по умолчанию)
const error = ref('');                           // текст ошибки

// Отправка формы
const submit = async () => {
    // Простая клиентская валидация: оба поля не должны быть пустыми
    if (!form.value.email || !form.value.password) {
        error.value = 'Заполните email и пароль';
        return;
    }

    try {
        // POST-запрос к /api/login с email/паролем
        const response = await axios.post('/login', form.value);
        // Сохраняем полученный токен в localStorage (для последующих авторизованных запросов)
        localStorage.setItem('token', response.data.token);
        // Редиректим на страницу управления товарами (админка)
        router.visit('/admin/products');
    } catch (err) {
        // Обрабатываем ошибку: если бэкенд вернул сообщение — показываем его, иначе общее сообщение
        error.value = err.response?.data?.message || 'Ошибка входа. Проверьте email и пароль.';
    }
};
</script>
