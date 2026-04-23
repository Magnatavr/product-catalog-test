<template>
    <MainLayout>
        <div class="flex justify-center items-center min-h-[70vh]">
            <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
                <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Вход в админ-панель</h2>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" v-model="form.email"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="admin@example.com" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Пароль</label>
                        <input type="password" v-model="form.password"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="••••••••" />
                    </div>

                    <button type="submit"
                            class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 transition focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Войти
                    </button>

                    <div v-if="error" class="text-red-600 text-sm text-center mt-2">
                        {{ error }}
                    </div>
                </form>

                <!-- Подсказка для тестирования (можно убрать) -->
                <div class="mt-4 text-xs text-gray-500 text-center border-t pt-4">
                    Тестовые данные: admin@example.com / password
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { ref } from 'vue';
import axios from '@/axios';
import { router } from '@inertiajs/vue3';

// Поля теперь пустые по умолчанию
const form = ref({ email: '', password: '' });
const error = ref('');

const submit = async () => {
    // Простая валидация на фронте
    if (!form.value.email || !form.value.password) {
        error.value = 'Заполните email и пароль';
        return;
    }

    try {
        const response = await axios.post('/login', form.value);
        localStorage.setItem('token', response.data.token);
        router.visit('/admin/products');
    } catch (err) {
        error.value = err.response?.data?.message || 'Ошибка входа. Проверьте email и пароль.';
    }
};
</script>
